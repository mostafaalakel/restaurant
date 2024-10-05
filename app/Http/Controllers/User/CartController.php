<?php

namespace App\Http\Controllers\User;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Resources\CartItemsResource;
use Illuminate\Support\Facades\Validator;


class CartController extends Controller
{
    use ApiResponseTrait;

    public function showCartInfo()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart || $cart->cartItems()->count() == 0) {
            return $this->retrievedResponse(null, "Your cart is empty");
        }

        $cartItems = $cart->cartItems()->with('food:id,name,price,image')->get();
        $cartItemsResource = CartItemsResource::collection($cartItems);
        $totalPrice = $cartItems->sum(fn($item) => $item->quantity * $item->food->price);

        return $this->retrievedResponse([
            'cartItems' => $cartItemsResource,
            'totalPrice' => $totalPrice . "$"
        ], "Cart items retrieved successfully");
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

        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            return $this->notFoundResponse("Cart not found for the user");
        }

        $cartItem = $cart->cartItems()->where('food_id', $request->food_id)->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $request->quantity);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'food_id' => $request->food_id,
                'quantity' => $request->quantity
            ]);
        }

        return $this->createdResponse(null, "Item added to cart successfully");
    }

    public function deleteItem($id)
    {
        $item = CartItem::findOrFail($id);
        $item->delete();

        return $this->deletedResponse("Item deleted from cart successfully");
    }

    public function updateItemQuantity(Request $request, $id)
    {
        $rules = [
            'quantity' => 'required|integer|min:1'
        ];

        $validate = Validator::make($request->only('quantity'), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $item = CartItem::findOrFail($id);
        $item->update([
            'quantity' => $request->quantity
        ]);

        return $this->updatedResponse(null, "Item updated successfully");
    }
}
