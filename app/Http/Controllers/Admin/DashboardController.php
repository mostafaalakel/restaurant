<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\baseTrait\FoodTrait;
use App\Http\Traits\baseTrait\OrderTrait;
use App\Http\Traits\baseTrait\CategoryTrait;
use App\Http\Traits\baseTrait\ReservationTrait;
use App\Models\Food;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\category;

class DashboardController extends Controller
{

    public function index()
    {
        $homeDashboard = [
            'numbers of categories' => category::count(),
            'numbers of foods' => Food::count(),
            'numbers of orders' => Order::where('order_status', 'processing')->count(),
            'numbers of reservations' => Reservation::where('status', 'processing')->count()
        ];
        return $this->retrievedResponse($homeDashboard, 'Home Dashboard retrieved successfully');
    }
}
