<?php

namespace App\Services\Admin;

use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderService
{
    public function showOrdersWithFilter(Request $request)
    {
        $query = Order::query();

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        return OrderResource::collection($query->paginate(10));
    }

    public function orderDetails($orderId)
    {
        $orderItems = OrderItem::where('order_id', $orderId)->with('food')->get();

        if ($orderItems->isEmpty()) {
            return ['status' => 'error', 'message' => 'Order has no items'];
        }

        return ['status' => 'success', 'data' => OrderItemResource::collection($orderItems)];
    }

    public function updateOrderStatus(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'order_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ['status' => 'error', 'errors' => $validator->errors()];
        }

        $order = Order::find($orderId);

        if (!$order) {
            return ['status' => 'error', 'message' => 'Order not found'];
        }

        $order->update(['order_status' => $request->order_status]);

        return ['status' => 'success', 'message' => 'Order status updated successfully'];
    }
}
