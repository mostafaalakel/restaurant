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

class AdminController extends Controller
{
    use OrderTrait, FoodTrait, CategoryTrait, ReservationTrait, ApiResponseTrait;
    public function index()
    {
        $homeDashboard = [
            'numbers of categories' =>  $categories_count = category::count(),
            'numbers of foods' => $foods_count = Food::count(),
            'numbers of orders' =>  $orders_count = Order::where('order_status', 'processing')->count(),
            'numbers of reservations' =>   $reservations_count = Reservation::where('status', 'processing')->count()
        ];
        return $this->retrievedResponse($homeDashboard, 'Home Dashboard retrieved successfully');
    }
}
