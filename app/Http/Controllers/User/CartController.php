<?php

namespace App\Http\Controllers\User;

use App\Models\Cart;
use App\Models\CodeDiscount;
use App\Models\Food;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class CartController extends Controller
{
    use ApiResponseTrait;

    public function showCartInfo()
    {
        $userId = Auth::guard('user')->id();
        $cart = Cart::with(['cartItems.food', 'cartItems.generalDiscounts', 'cartItems.codeDiscounts'])
            ->where('user_id', $userId)
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return $this->retrievedResponse(null, "Your cart is empty");
        }

        $cartItems = $cart->cartItems->map(function ($cartItem) {
            $foodPrice = $cartItem->food->price;
            $priceAfterDiscount = $this->calculateDiscountedPrice($cartItem, $foodPrice);

            return [
                'cart_item_id' => $cartItem->id,
                'food_id' => $cartItem->food->id,
                'food_name' => $cartItem->food->getTranslation('name' , app()->getLocale()),
                'food_price' => number_format($cartItem->food->price, 2) . " $",
                'food_image' => asset('upload/food_images/' . $cartItem->food->image),
                'quantity' => $cartItem->quantity,
                'price_after_discount' => number_format($priceAfterDiscount, 2) . " $",
                'general_discounts' => $cartItem->generalDiscounts
                    ->filter(function ($discount) use ($cartItem) {
                        return $this->isValidDiscount($discount) && $cartItem->food->generalDiscounts->contains($discount);
                    })
                    ->map(function ($discount) {
                        return [
                            'discount_name' => $discount->getTranslation('name', app()->getLocale()),
                            'discount_value' => $discount->value . "%",
                            'start_date' => $discount->start_date,
                            'end_date' => $discount->end_date,
                        ];
                    }),

                'code_discounts' => $cartItem->codeDiscounts
                    ->filter(function ($codeDiscount) use ($cartItem) {
                        return $this->isValidDiscount($codeDiscount) && $cartItem->food->codeDiscounts->contains($codeDiscount);
                    })
                    ->map(function ($codeDiscount) {
                        return [
                            'code_discount_name' => $codeDiscount->name,
                            'code_discount_value' => $codeDiscount->value . "%",
                        ];
                    }),

            ];
        });

        $totalPrice = $cartItems->sum(function ($item) {
            $price = floatval(preg_replace('/[^0-9.]/', '', $item['price_after_discount']));
            $quantity = intval($item['quantity']);
            return $price * $quantity;
        });



        return response()->json([
            'success' => true,
            'message' => 'Cart details retrieved successfully',
            'data' => [
                'cart_items' => $cartItems,
                'total_price' => number_format($totalPrice, 2) . " $",
            ],
        ], 200);
    }


    private function calculateDiscountedPrice($cartItem, $foodPrice)
    {
        $priceAfterDiscount = $foodPrice;

        foreach ($cartItem->generalDiscounts as $generalDiscount) {
            if ($this->isValidDiscount($generalDiscount) && $cartItem->food->generalDiscounts->contains($generalDiscount)) {
                $priceAfterDiscount -= $priceAfterDiscount * ($generalDiscount->value / 100);
            }
        }

        foreach ($cartItem->codeDiscounts as $codeDiscount) {
            if ($this->isValidDiscount($codeDiscount) &&
                $cartItem->food->codeDiscounts->contains($codeDiscount)) {
                $priceAfterDiscount -= $priceAfterDiscount * ($codeDiscount->value / 100);
            }
        }

        return max($priceAfterDiscount, 0);
    }


    private function isValidDiscount($discount)
    {
        return $discount->is_active == 1 &&
            $discount->start_date <= now() &&
            $discount->end_date >= now();
    }

    public function applyDiscountCode(Request $request)
    {
        $cart = Cart::where('user_id', Auth::guard('user')->user()->id)->first();

        if (!$cart || $cart->cartItems()->count() == 0) {
            return $this->retrievedResponse(null, "Your cart is empty");
        }

        $code = $request->input('discount_code');

        $codeDiscount = CodeDiscount::where('code', $code)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$codeDiscount) {
            return $this->retrievedResponse(null, "Invalid or expired discount code.");
        }


        $appliedToAnyItem = false;

        foreach ($cart->cartItems as $cartItem) {
            $alreadyApplied = $cartItem->codeDiscounts->contains($codeDiscount->id);

            if ($alreadyApplied) {
                continue;
            }

            $isCodeApplicable = DB::table('food_code_discount')
                ->where('food_id', $cartItem->food_id)
                ->where('code_discount_id', $codeDiscount->id)
                ->exists();

            if ($isCodeApplicable) {
                $cartItem->codeDiscounts()->attach($codeDiscount->id);
                $appliedToAnyItem = true;
            }
        }

        if (!$appliedToAnyItem) {
            return $this->retrievedResponse(null, "This discount code is not applicable to any items in your cart.");
        }
        return $this->retrievedResponse(null, "Discount code applied successfully to applicable items.");
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

        $cart = Cart::where('user_id', Auth::guard('user')->user()->id)->first();
        if (!$cart) {
            return $this->notFoundResponse("Cart not found for the user");
        }

        $cartItemCheck = $cart->cartItems()->where('food_id', $request->food_id)->first();

        if ($cartItemCheck) {
            $cartItemCheck->increment('quantity', $request->quantity);
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'food_id' => $request->food_id,
                'quantity' => $request->quantity
            ]);
            $food = Food::find($request->food_id);
            $foodGeneralDiscounts = $food->generalDiscounts()->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();

            if ($foodGeneralDiscounts->isNotEmpty()) {
                $cartItem->generalDiscounts()->attach($foodGeneralDiscounts->pluck('id')->toArray());
            }
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

