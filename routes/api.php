<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\FoodController;
use App\Http\Controllers\User\UserAuthController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\User\ReservationController;
use App\Http\Controllers\superAdmin\superAdminController;
use App\Http\Controllers\superAdmin\superAdminAuthController;
use App\Http\Controllers\Admin\FoodController as AdminFoodController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\User\MenuController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/home', [HomeController::class, 'index']);
Route::get('/food/details/{id}', [FoodController::class, 'FoodDetails']);


Route::group(["prefix" => 'menu'], function () {
    Route::get('/category', [MenuController::class, 'showCategories']);
    Route::get('/foods/{category_id}', [MenuController::class, 'showFoodOfCategory']);
});


Route::group(["prefix" => 'user'], function () {
    Route::post('login', [UserAuthController::class, 'login']);
    Route::post('register', [UserAuthController::class, 'register']);
    Route::middleware('auth:user')->get('me', [UserAuthController::class, 'me']);
    Route::middleware('auth:user')->post('logout', [UserAuthController::class, 'logout']);
    Route::middleware('auth:user')->post('refresh', [UserAuthController::class, 'refresh']);
});
Route::get('/auth/redirect/google', [UserAuthController::class, 'redirectToGoogle']);
Route::get('/auth/callback/google', [UserAuthController::class, 'handleGoogleCallback']);


// Routes for Admins
//auth
Route::group(['prefix' => 'admin'], function () {
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::middleware('auth:admin')->get('me', [AdminAuthController::class, 'me']);
    Route::middleware('auth:admin')->post('logout', [AdminAuthController::class, 'logout']);
    Route::middleware('auth:admin')->post('refresh', [AdminAuthController::class, 'refresh']);
});


Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function () {
    Route::get('/dashboard', [AdminController::class, 'index']);
    ///////////////////////Orders
    Route::get('/showOrders', [AdminController::class, 'showOrders']);
    Route::get('/orderDetails/{order_id}', [AdminController::class, 'orderDetails']);
    Route::patch('/updateOrderStatus/{order_id}', [AdminController::class, 'updateOrderStatus']);
    /////////////////Reservation
    Route::get('/showReservation', [AdminController::class, 'showReservation']);
    Route::patch('/updateReservationStatus/{reservation_id}', [AdminController::class, 'updateReservationStatus']);
    ////////////////////////Category
    Route::get('/showCategories', [AdminController::class, 'showCategories']);
    Route::post('/addCategory', [AdminController::class, 'addCategory']);
    Route::patch('/updateCategory/{category_id}', [AdminController::class, 'updateCategory']);
    Route::delete('/deleteCategory/{category_id}', [AdminController::class, 'deleteCategory']);
    ///////////////////////////food
    Route::get('/showFoods', [AdminController::class, 'showFoods']);
    Route::post('/addFood', [AdminController::class, 'addFood']);
    Route::patch('/updateFood/{food_id}', [AdminController::class, 'updateFood']);
    Route::delete('/deleteFood/{food_id}', [AdminController::class, 'deleteFood']);
});



//////////////////////////Route for superAdmin
Route::group(['prefix' => 'superAdmin'], function () {
    Route::post('login', [superAdminAuthController::class, 'login']);
    Route::post('register', [superAdminAuthController::class, 'register']);
    Route::middleware('auth:superAdmin')->get('me', [superAdminAuthController::class, 'me']);
    Route::middleware('auth:superAdmin')->post('logout', [superAdminAuthController::class, 'logout']);
    Route::middleware('auth:superAdmin')->post('refresh', [superAdminAuthController::class, 'refresh']);
});

Route::group(['prefix' => 'superAdmin', 'middleware' => 'auth:superAdmin'], function () {
    Route::get('/dashboard', [superAdminController::class, 'index']);
    ///////////////////////Orders
    Route::get('/showOrders', [superAdminController::class, 'showOrders']);
    Route::get('/orderDetails/{order_id}', [superAdminController::class, 'orderDetails']);
    Route::patch('/updateOrderStatus/{order_id}', [superAdminController::class, 'updateOrderStatus']);
    /////////////////Reservation
    Route::get('/showReservation', [superAdminController::class, 'showReservation']);
    Route::patch('/updateReservation/{reservation_id}', [superAdminController::class, 'updateReservation']);
    ////////////////////////Category
    Route::get('/showCategories', [superAdminController::class, 'showCategories']);
    Route::post('/addCategory', [superAdminController::class, 'addCategory']);
    Route::patch('/updateCategory/{category_id}', [superAdminController::class, 'updateCategory']);
    Route::delete('/deleteCategory/{category_id}', [superAdminController::class, 'deleteCategory']);
    ///////////////////////////food
    Route::get('/showFoods', [superAdminController::class, 'showFoods']);
    Route::post('/addFood', [superAdminController::class, 'addFood']);
    Route::patch('/updateFood/{food_id}', [superAdminController::class, 'updateFood']);
    Route::delete('/deleteFood/{food_id}', [superAdminController::class, 'deleteFood']);
    /////////////////manage user
    Route::get('/showAdmins', [superAdminController::class, 'showAdmins']);
    Route::post('/addAdmin', [superAdminController::class, 'addAdmin']);
    Route::delete('/deleteAdmin/{admin_id}', [superAdminController::class, 'deleteAdmin']);
});

// Routes for cart
Route::group(['prefix' => 'cart', 'middleware' => 'auth:user'], function () {
    Route::post('addToCart', [CartController::class, 'AddToCart']);
    Route::get('showCart', [CartController::class, 'ShowCartInfo']);
    Route::delete('deleteItemCart/{cartItemId}', [CartController::class, 'DeleteItem']);
    Route::patch('updateItemCart/{cartItemId}', [CartController::class, 'UpdateItemQuantity']);
});

// Routes for order
Route::group(['prefix' => '/order', 'middleware' => 'auth:user'], function () {
    Route::post('/create', [OrderController::class, 'createOrder']);
    Route::get('/myOrders', [OrderController::class, 'myOrders']);
    Route::get('/myOrderDetails/{orderId}', [OrderController::class, 'myOrderDetails']);
    Route::post('/retry-payment/{orderId}', [OrderController::class, 'retryPayment']);
});
Route::get('payment-success/{order}', [OrderController::class, 'paymentSuccess'])->name('payment.success');
Route::get('payment-cancel/{order}', [OrderController::class, 'paymentCancel'])->name('payment.cancel');


// Routes for reservation
Route::group(['prefix' => 'reservation', 'middleware' => 'auth:user'], function () {
    Route::post('create', [ReservationController::class, 'createReservation']);
    Route::get('showReservation/{user_id}', [ReservationController::class, 'showReservation']);
    Route::delete('delete/{id}', [ReservationController::class, 'deleteReservation']);
    Route::patch('update/{id}', [ReservationController::class, 'updateReservation']);
});

// Routes for review
Route::post('/review/addReviews', [ReviewController::class, 'AddReviews'])->middleware('auth:user');
Route::get('/review/show/{food_item_id}', [ReviewController::class, 'showReviews']);
