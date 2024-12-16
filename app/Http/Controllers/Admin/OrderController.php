<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function showOrdersWithFilter(Request $request)
    {
        $query=Order::query();
        if($request->has('payment_status')){
            $query->where('payment_status',$request->get('payment_status'));
        }
        if($request->has('order_status')){
            $query->where('order_status',$request->get('order_status'));
        }

        $orders = $query->paginate(10);
        return OrderResource::collection($orders);
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

    public function updateOrderStatus(Request $request, $order_id)
    {
        $rules = ['order_status' => 'required'];
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $order_status = Order::findOrFail($order_id);
        $order_status->update([
            'order_status' => $request->order_status
        ]);

        return $this->updatedResponse(null, 'order status updated successfully');
    }
}
