<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Admin\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponseTrait;

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function showOrdersWithFilter(Request $request)
    {
        return $this->retrievedResponse($this->orderService->showOrdersWithFilter($request));
    }

    public function orderDetails($orderId)
    {
        $result = $this->orderService->orderDetails($orderId);

        if ($result['status'] === 'error') {
            return $this->notFoundResponse($result['message']);
        }

        return $this->retrievedResponse($result['data'], 'Order items retrieved successfully');
    }

    public function updateOrderStatus(Request $request, $orderId)
    {
        $result = $this->orderService->updateOrderStatus($request, $orderId);

        if ($result['status'] === 'error') {
            return isset($result['errors'])
                ? $this->validationErrorResponse($result['errors'])
                : $this->notFoundResponse($result['message']);
        }

        return $this->updatedResponse(null, $result['message']);
    }
}
