<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Resources\OrderItemResource;
use Illuminate\Support\Facades\Validator;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class OrderController extends Controller
{
    use ApiResponseTrait;
    public function createOrder(Request $request)
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart || $cart->cartItems()->count() === 0) {
            return $this->notFoundResponse("Your cart is empty");
        }

        $rules = [
            'country' => 'required',
            'address' => 'required',
            'town' => 'required',
            'zipCode' => 'required',
            'phone_number' => 'required',
            'total_price' => 'required|numeric',
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $order = $this->createNewOrder($request);
            $this->createOrderItems($order, $cart->id);
            CartItem::where('cart_id', $cart->id)->delete();

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $paypalToken = $provider->getAccessToken();
            $provider->setAccessToken($paypalToken);

            $response = $this->createPayPalOrder($provider, $order->total_price, $order->id);

            if (isset($response['id']) && $response['status'] == 'CREATED') {
                DB::commit();
                return $this->createdResponse([
                    'order_id' => $order->id,
                    'paypal_order_id' => $response['id'],
                    'approve_url' => $response['links'][1]['href']
                ], 'Order created successfully');
            } else {
                throw new Exception('Error in creating PayPal order');
            }
        } catch (Exception $e) {
            DB::rollback();
            $order->payment_status = 'pending';
            $order->save();
            return $this->apiResponse('error', $e->getMessage(), null, 500);
        }
    }

    private function createNewOrder(Request $request)
    {
        return Order::create([
            'user_id' => Auth::id(),
            'country' => $request->country,
            'address' => $request->address,
            'town' => $request->town,
            'zipCode' => $request->zipCode,
            'phone_number' => $request->phone_number,
            'total_price' => $request->total_price,
        ]);
    }

    private function createOrderItems(Order $order, $cartId)
    {
        $cartItems = CartItem::where('cart_id', $cartId)->get();
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'food_id' => $cartItem->food->id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->food->price * $cartItem->quantity,
            ]);
        }
    }

    private function createPayPalOrder($provider, $totalPrice, $orderId)
    {
        return $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $totalPrice
                    ]
                ]
            ],
            "application_context" => [
                "return_url" => route('payment.success', ['order' => $orderId]),
                "cancel_url" => route('payment.cancel', ['order' => $orderId]),
            ]
        ]);
    }

    public function paymentSuccess(Request $request, $orderId)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $response = $provider->capturePaymentOrder($request->token);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $order = Order::find($orderId);
            $order->payment_status = 'paid';
            $order->save();

            return $this->retrievedResponse(null, 'Payment successful');
        } else {
            return $this->apiResponse('error', 'Payment failed', null, 500);
        }
    }

    public function paymentCancel(Request $request, $orderId)
    {
        return $this->retrievedResponse(['orderId' => $orderId], 'Payment canceled');
    }

    public function myOrders()
    {
        $orders = Auth::user()->orders;

        if ($orders->isEmpty()) {
            return $this->retrievedResponse([], 'You have no orders yet');
        }

        $ordersResource = OrderResource::collection($orders);
        return $this->retrievedResponse($ordersResource, 'Your orders retrieved successfully');
    }

    public function myOrderDetails($id)
    {
        $orderItems = OrderItem::where('order_id', $id)->with('food')->get();

        if ($orderItems->isEmpty()) {
            return $this->retrievedResponse([], 'Your order has no items');
        }

        $orderItemsResource = OrderItemResource::collection($orderItems);
        return $this->retrievedResponse($orderItemsResource, 'Your order items retrieved successfully');
    }
    public function retryPayment($orderId)
    {
        $order = Order::find($orderId);

        if (!$order || $order->payment_status == 'paid') {
            return $this->apiResponse('error', 'Order is not available for retry or already paid.', null, 400);
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $provider->setAccessToken($paypalToken);

        $response = $this->createPayPalOrder($provider, $order->total_price, $order->id);

        if (isset($response['id']) && $response['status'] == 'CREATED') {
            return $this->retrievedResponse([
                'order_id' => $order->id,
                'paypal_order_id' => $response['id'],
                'approve_url' => $response['links'][1]['href']
            ], 'Order payment retry initiated successfully');
        } else {
            return $this->apiResponse('error', 'Error in retrying PayPal order', null, 500);
        }
    }
}
