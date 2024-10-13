<?php

namespace App\Http\Traits\baseTrait;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\OrderItem;
use App\Http\Resources\OrderResource;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Resources\OrderItemResource;
use Illuminate\Support\Facades\Validator;

trait OrderTrait
{
    use ApiResponseTrait;

    public function showOrders()
    {
        $orders = OrderResource::collection(Order::orderBy('id', 'desc')->with('user')->get());
        if ($orders->isEmpty()) {
            return $this->notFoundResponse('You have no orders yet');
        }
        return $this->retrievedResponse($orders, 'orders retrieved successfully');
    }

    public function orderDetails($order_id)
    {
        $orderItems = OrderItem::where('order_id', $order_id)->with('food')->get();
        if ($orderItems->isEmpty()) {
            return $this->notFoundResponse('Order has no items');
        }

        $orderItems = OrderItemResource::collection($orderItems);
        return $this->retrievedResponse($orderItems, 'Order items retrieved successfully');
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $rules = ['order_status' => 'required'];
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $order_status = Order::findOrFail($id);
        $order_status->update([
            'order_status' => $request->order_status
        ]);

        return $this->updatedResponse(null, 'order status updated successfully');
    }
}
