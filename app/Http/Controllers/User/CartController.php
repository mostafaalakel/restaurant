<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\User\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use ApiResponseTrait;

    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function showCartInfo()
    {
        $userId = Auth::guard('user')->id();
        $cartInfo = $this->cartService->getCartInfo($userId);

        if (!$cartInfo) {
            return $this->retrievedResponse(null, "Your cart is empty");
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart details retrieved successfully',
            'data' => $cartInfo,
        ], 200);
    }

    public function applyDiscountCode(Request $request)
    {
        $userId = Auth::guard('user')->id();
        $code = $request->input('discount_code');

        $result = $this->cartService->applyDiscountCode($userId, $code);

        return $this->retrievedResponse(null, $result);
    }

    public function addToCart(Request $request)
    {
        $rules = [
            'food_id' => 'required|exists:foods,id',
            'quantity' => 'required|integer|min:1'
        ];

        $validate = Validator::make($request->only('food_id', 'quantity'), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $userId = Auth::guard('user')->id();
        $result = $this->cartService->addToCart($userId, $request->food_id, $request->quantity);

        return $this->createdResponse(null, $result);
    }

    public function deleteItem($cartItemId)
    {
        $result = $this->cartService->deleteItem($cartItemId);

        return $this->deletedResponse($result);
    }

    public function updateItemQuantity(Request $request, $cartItemId)
    {
        $rules = [
            'quantity' => 'required|integer|min:1'
        ];

        $validate = Validator::make($request->only('quantity'), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $result = $this->cartService->updateItemQuantity($cartItemId, $request->quantity);

        return $this->updatedResponse(null, $result);
    }
}
