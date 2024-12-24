<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Food;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\category;

class DashboardController extends Controller
{
    use ApiResponseTrait;
    public function index()
    {
        $homeDashboard = [
            'numbers of categories' => category::count(),
            'numbers of foods' => Food::count(),
            'numbers of processing orders' => Order::where('order_status', 'processing')->count(),
            'numbers of processing reservations' => Reservation::where('status', 'processing')->count(),
            'numbers of employees' => Employee::count(),
        ];
        return $this->retrievedResponse($homeDashboard, 'Home Dashboard retrieved successfully');
    }
}
