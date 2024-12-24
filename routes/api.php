<?php

use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\FoodController;
use App\Http\Controllers\User\UserAuthController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\User\ReservationController;
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


Route::group(['middleware' => 'setLocale'], function () {
    Route::get('/home', [HomeController::class, 'index']);

    //route for foods
    Route::group(['prefix' => 'food'], function () {
        Route::get('/discounts', [FoodController::class, 'foodDiscount']);
        Route::get('/filter', [FoodController::class, 'foodFilter']);
        Route::get('/details/{food_id}', [FoodController::class, 'FoodDetails']);
    });

    //route for menu
    Route::group(['prefix' => 'menu'], function () {
        Route::get('/category', [MenuController::class, 'showCategories']);
        Route::get('/foods/{category_id}', [MenuController::class, 'showFoodOfCategory']);
    });
});

// Routes for review
Route::post('/review/addReviews', [ReviewController::class, 'AddReviews']);
Route::get('/review/show/{food_item_id}', [ReviewController::class, 'showReviews']);


// Routes with 'auth:user' middleware
Route::group(['middleware' => 'auth:user'], function () {

    // Routes for cart
    Route::group(['prefix' => 'cart'], function () {
        Route::post('addToCart', [CartController::class, 'AddToCart']);
        Route::post('applyDiscountCode', [CartController::class, 'applyDiscountCode']);
        Route::get('showCart', [CartController::class, 'ShowCartInfo'])->middleware('setLocale');
        Route::delete('deleteItemCart/{cartItemId}', [CartController::class, 'DeleteItem']);
        Route::patch('updateItemCart/{cartItemId}', [CartController::class, 'UpdateItemQuantity']);
    });

    // Routes for order
    Route::group(['prefix' => 'order'], function () {
        Route::post('create', [OrderController::class, 'createOrder']);
        Route::get('myOrders', [OrderController::class, 'myOrders']);
        Route::get('myOrderDetails/{orderId}', [OrderController::class, 'myOrderDetails']);
        Route::post('retry-payment/{orderId}', [OrderController::class, 'retryPayment']);
    });

    // Routes for reservation
    Route::group(['prefix' => 'reservation'], function () {
        Route::post('create', [ReservationController::class, 'createReservation']);
        Route::get('showReservation', [ReservationController::class, 'showReservation']);
        Route::delete('delete/{reservation_id}', [ReservationController::class, 'deleteReservation']);
        Route::patch('update/{reservation_id}', [ReservationController::class, 'updateReservation']);
    });
});


Route::group(["prefix" => 'user'], function () {
    Route::post('login', [UserAuthController::class, 'login']);
    Route::post('register', [UserAuthController::class, 'register']);
    Route::middleware('auth:user')->post('logout', [UserAuthController::class, 'logout']);
    Route::middleware('auth:user')->post('refresh', [UserAuthController::class, 'refresh']);
});

Route::get('/auth/redirect/google', [UserAuthController::class, 'redirectToGoogle']);
Route::get('/auth/callback/google', [UserAuthController::class, 'handleGoogleCallback']);
Route::get('payment-success/{order}', [OrderController::class, 'paymentSuccess'])->name('payment.success');
Route::get('payment-cancel/{order}', [OrderController::class, 'paymentCancel'])->name('payment.cancel');


// Routes for Admins
//auth
Route::group(['prefix' => 'admin'], function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::middleware('auth:admin')->post('/logout', [AdminAuthController::class, 'logout']);
    Route::middleware('auth:admin')->post('/refresh', [AdminAuthController::class, 'refresh']);
});


Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/show', [\App\Http\Controllers\Admin\OrderController::class, 'showOrdersWithFilter']);
        Route::get('/details/{order_id}', [\App\Http\Controllers\Admin\OrderController::class, 'orderDetails']);
        Route::patch('/update-status/{order_id}', [\App\Http\Controllers\Admin\OrderController::class, 'updateOrderStatus']);
    });

    // Reservations
    Route::prefix('reservations')->group(function () {
        Route::get('/show', [\App\Http\Controllers\Admin\ReservationController::class, 'showReservation']);
        Route::patch('/update-status/{reservation_id}', [\App\Http\Controllers\Admin\ReservationController::class, 'updateReservationStatus']);
    });

    Route::group(['middleware' => 'setLocale'], function () {
        // Categories
        Route::prefix('categories')->group(function () {
            Route::get('/show', [\App\Http\Controllers\Admin\CategoryController::class, 'showCategories']);
            Route::post('/add', [\App\Http\Controllers\Admin\CategoryController::class, 'addCategory']);
            Route::patch('/update/{category_id}', [\App\Http\Controllers\Admin\CategoryController::class, 'updateCategory']);
            Route::delete('/delete/{category_id}', [\App\Http\Controllers\Admin\CategoryController::class, 'deleteCategory']);
        });

        // Foods
        Route::prefix('foods')->group(function () {
            Route::get('/show', [\App\Http\Controllers\Admin\FoodController::class, 'showFoods']);
            Route::post('/add', [\App\Http\Controllers\Admin\FoodController::class, 'addFood']);
            Route::patch('/update/{food_id}', [\App\Http\Controllers\Admin\FoodController::class, 'updateFood']);
            Route::delete('/delete/{food_id}', [\App\Http\Controllers\Admin\FoodController::class, 'deleteFood']);
        });
    });


    //generalDiscount
    Route::prefix('discountsGeneral')->group(function () {
        Route::get('/show', [DiscountController::class, 'getAllGeneralDiscounts'])->middleware('setLocale');
        Route::get('/{discount_general_id}/foods', [DiscountController::class, 'showFoodByGeneralDiscount'])->middleware('setLocale');
        Route::post('/store', [DiscountController::class, 'storeGeneralDiscount']);
        Route::patch('/update/{discount_general_id}', [DiscountController::class, 'updateGeneralDiscount']);
        Route::delete('/delete/{discount_general_id}', [DiscountController::class, 'deleteGeneralDiscount']);
        Route::post('/{discount_general_id}/attach-foods', [DiscountController::class, 'attachGeneralDiscountToFood']);
        Route::delete('/{discount_general_id}/detach-food', [DiscountController::class, 'detachFoodFromGeneralDiscount']);
    });

    // Code Discounts
    Route::prefix('/discountsCode')->group(function () {
        Route::get('/show', [DiscountController::class, 'getAllCodeDiscounts']);
        Route::get('/{discount_code_id}/foods', [DiscountController::class, 'showFoodByCodeDiscount'])->middleware('setLocale');
        Route::post('/store', [DiscountController::class, 'storeCodeDiscount']);
        Route::patch('/update/{discount_code_id}', [DiscountController::class, 'updateCodeDiscount']);
        Route::delete('/delete/{discount_code_id}', [DiscountController::class, 'deleteCodeDiscount']);
        Route::post('/{discount_code_id}/attach-foods', [DiscountController::class, 'attachCodeDiscountToFood']);
        Route::delete('/{discount_code_id}/detach-food', [DiscountController::class, 'detachFoodFromCodeDiscount']);

    });

    ////////////////////employees
    Route::prefix('employees')->group(function () {
        Route::get('/show', [EmployeeController::class, 'showEmployees']);
        Route::post('/add', [EmployeeController::class, 'addEmployee']);
        Route::patch('/update/{employee_id}', [EmployeeController::class, 'updateEmployee']);
        Route::delete('/delete/{employee_id}', [EmployeeController::class, 'deleteEmployee']);
    });

});
