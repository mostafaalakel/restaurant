<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\User\OrderService;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use ApiResponseTrait;

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function createOrder(Request $request)
    {
        $result = $this->orderService->createOrder($request);

        if ($result['status'] == 'error') {
            return $this->validationErrorResponse([
                'message' => $result['message'],
                'details' => $result['details'] ?? null
            ]);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    public function paymentSuccess(Request $request, $orderId)
    {
        $result = $this->orderService->paymentSuccess($request, $orderId);

        if ($result['status'] == 'error') {
            return $this->apiResponse('error', $result['message']);
        }

        return $this->retrievedResponse(null, 'Payment successful');
    }

    public function paymentCancel(Request $request, $orderId)
    {
        return $this->retrievedResponse(['orderId' => $orderId], 'Payment canceled');
    }

    public function myOrders()
    {
        $orders = $this->orderService->myOrders();

        if ($orders->isEmpty()) {
            return $this->retrievedResponse([], 'You have no orders yet');
        }

        return $this->retrievedResponse(OrderResource::collection($orders), 'Your orders retrieved successfully');
    }

    public function myOrderDetails($orderId)
    {
        $orderItems = $this->orderService->myOrderDetails($orderId);

        if ($orderItems->isEmpty()) {
            return $this->retrievedResponse([], 'Your order has no items');
        }

        return $this->retrievedResponse(OrderItemResource::collection($orderItems), 'Your order items retrieved successfully');
    }

    public function retryPayment($orderId)
    {
        $order = Order::find($orderId);

        if (!$order || $order->payment_status == 'paid') {
            return $this->apiResponse('error', 'Order is not available for retry or already paid.');
        }

        $result = $this->orderService->retryPayment($orderId);

        if ($result['status'] == 'error') {
            return $this->apiResponse('error', $result['message']);
        }

        return $this->retrievedResponse($result['data'], 'Order payment retry initiated successfully');
    }
}
