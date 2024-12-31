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
        $cart = Cart::where('user_id', Auth::guard('user')->id())->first();
        $cartItems = $cart->cartItems()->get();

        if ($cartItems->count() === 0) {
            return $this->notFoundResponse("Your cart is empty");
        }

        $insufficientStockItems = [];

        foreach ($cartItems as $cartItem) {
            if ($cartItem->quantity > $cartItem->food->stock) {
                $insufficientStockItems[] = [
                    'item' => $cartItem->food->name,
                    'requested_quantity' => $cartItem->quantity,
                    'available_stock' => $cartItem->food->stock,
                ];
            }
        }

        if (!empty($insufficientStockItems)) {
            return $this->validationErrorResponse([
                'message' => 'Some items do not have enough stock.',
                'details' => $insufficientStockItems,
            ]);
        }


        $rules = [
            'country' => 'required',
            'address' => 'required',
            'town' => 'required',
            'zipCode' => 'required',
            'phone_number' => 'required'
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        DB::beginTransaction();
        try {
            $order = $this->createNewOrder($request , $cartItems);
            $this->createOrderItems($order, $cart->id);
            CartItem::where('cart_id', $cart->id)->delete();

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $paypalToken = $provider->getAccessToken();
            $provider->setAccessToken($paypalToken);

            $response = $this->createPayPalOrder($provider, $order->price_after_discounts, $order->id);

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
            if (isset($order)) {
                $order->payment_status = 'pending';
                $order->save();
            }
            return $this->apiResponse('error', $e->getMessage(), null, 500);
        }
    }

    public function createNewOrder(Request $request, $cartItems)
    {
        $price = $cartItems->sum(function ($cartItem) {
            return $cartItem->quantity * $cartItem->food->price;
        });

        $price_after_discounts = $cartItems->sum(function ($cartItem) {
            $foodPrice = $cartItem->food->price;
            $discountedPrice = $this->calculateDiscountedPrice($cartItem, $foodPrice);
            return $discountedPrice * $cartItem->quantity;
        });


        return Order::create([
            'user_id' => Auth::guard('user')->id(),
            'country' => $request->country,
            'address' => $request->address,
            'town' => $request->town,
            'zipCode' => $request->zipCode,
            'phone_number' => $request->phone_number,
            'price' => $price,
            'price_after_discounts' => $price_after_discounts,
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
                'price_after_discounts' => $this->calculateDiscountedPrice($cartItem, $cartItem->food->price) * $cartItem->quantity,
            ]);
            $this->updateStock($cartItem);
        }
    }

    private function updateStock($cartItem)
    {
        $food = $cartItem->food;
        $food->stock -= $cartItem->quantity;
        $food->save();
    }


    private function calculateDiscountedPrice($cartItem, $foodPrice)
    {
        $priceAfterDiscount = $foodPrice;

        foreach ($cartItem->generalDiscounts as $generalDiscount) {
            if ($this->isValidDiscount($generalDiscount) && $cartItem->food->generalDiscounts->contains($generalDiscount)) {
                $priceAfterDiscount -= $priceAfterDiscount * ($generalDiscount->value / 100);
            }
        }

        foreach ($cartItem->codeDiscounts as $codeDiscount) {
            if ($this->isValidDiscount($codeDiscount) &&
                $cartItem->food->codeDiscounts->contains($codeDiscount)) {
                $priceAfterDiscount -= $priceAfterDiscount * ($codeDiscount->value / 100);
            }
        }

        return max($priceAfterDiscount, 0);
    }

    private function isValidDiscount($discount)
    {
        return $discount->is_active == 1 &&
            $discount->start_date <= now() &&
            $discount->end_date >= now();
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
        $orders = Auth::user()->orders()->with('user:id,name')->get();

        if ($orders->isEmpty()) {
            return $this->retrievedResponse([], 'You have no orders yet');
        }

        $ordersResource = OrderResource::collection($orders);
        return $this->retrievedResponse($ordersResource, 'Your orders retrieved successfully');
    }

    public function myOrderDetails($orderId)
    {
        $orderItems = OrderItem::where('order_id', $orderId)->with('food')->get();

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

        $response = $this->createPayPalOrder($provider, $order->price_after_discounts, $order->id);

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
