<?php

namespace App\Http\Controllers\superAdmin;

use App\Models\Food;
use App\Models\Admin;
use App\Models\Order;
use App\Models\category;
use App\Models\SuperAdmin;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\baseTrait\FoodTrait;
use App\Http\Traits\baseTrait\OrderTrait;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\baseTrait\CategoryTrait;
use App\Http\Traits\baseTrait\ReservationTrait;

class superAdminController extends Controller
{
    use OrderTrait, FoodTrait, CategoryTrait, ReservationTrait;

    public function index()
    {
        $homeDashboard = [
            'numbers of categories' =>  $categories_count = category::count(),
            'numbers of foods' => $foods_count = Food::count(),
            'numbers of orders' =>  $orders_count = Order::where('order_status', 'processing')->count(),
            'numbers of reservations' =>   $reservations_count = Reservation::where('status', 'processing')->count(),
            'numbers of Admins' =>   $Admins_count = Admin::all()->count(),
        ];
        return $this->retrievedResponse($homeDashboard, 'Home Dashboard retrieved successfully');
    }

    public function addAdmin(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:6',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $Admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return $this->createdResponse(null, 'admin created successfully');
    }

    public function showAdmins()
    {
        $admin = UserResource::collection(Admin::all());
        return $this->retrievedResponse($admin, "admins info retrieved successfully ");
    }

    public function deleteAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();
        return $this->deletedResponse('admin deleted successfully');
    }
}
