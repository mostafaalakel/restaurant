<?php

namespace App\Services\User;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CodeDiscount;
use App\Models\Food;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getCartInfo($userId)
    {
        $cart = Cart::with(['cartItems.food', 'cartItems.generalDiscounts', 'cartItems.codeDiscounts'])
            ->where('user_id', $userId)
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return null;
        }

        $cartItems = $cart->cartItems->map(function ($cartItem) {
            $foodPrice = $cartItem->food->price;
            $priceAfterDiscount = $this->calculateDiscountedPrice($cartItem, $foodPrice);

            return [
                'cart_item_id' => $cartItem->id,
                'food_id' => $cartItem->food->id,
                'food_name' => $cartItem->food->getTranslation('name', app()->getLocale()),
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

        return [
            'cart_items' => $cartItems,
            'total_price' => number_format($totalPrice, 2) . " $",
        ];
    }

    public function calculateDiscountedPrice($cartItem, $foodPrice)
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

    public function isValidDiscount($discount)
    {
        return $discount->is_active == 1 &&
            $discount->start_date <= now() &&
            $discount->end_date >= now();
    }

    public function applyDiscountCode($userId, $code)
    {
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart || $cart->cartItems()->count() == 0) {
            return "Your cart is empty";
        }

        $codeDiscount = CodeDiscount::where('code', $code)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$codeDiscount) {
            return "Invalid or expired discount code.";
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
            return "This discount code is not applicable to any items in your cart.";
        }

        return "Discount code applied successfully to applicable items.";
    }

    public function addToCart($userId, $foodId, $quantity)
    {
        $cart = Cart::where('user_id', $userId)->first();
        if (!$cart) {
            return "Cart not found for the user";
        }

        $cartItemCheck = $cart->cartItems()->where('food_id', $foodId)->first();

        if ($cartItemCheck) {
            $cartItemCheck->increment('quantity', $quantity);
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'food_id' => $foodId,
                'quantity' => $quantity
            ]);
            $food = Food::find($foodId);
            $foodGeneralDiscounts = $food->generalDiscounts()->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();

            if ($foodGeneralDiscounts->isNotEmpty()) {
                $cartItem->generalDiscounts()->attach($foodGeneralDiscounts->pluck('id')->toArray());
            }
        }

        return "Item added to cart successfully";
    }

    public function deleteItem($cartItemId)
    {
        $item = CartItem::find($cartItemId);
        if (!$item) {
            return ['status' => 'error', "message" => "Item not found"];
        }
        $item->delete();

        return "Item deleted from cart successfully";
    }

    public function updateItemQuantity($cartItemId, $quantity)
    {
        $item = CartItem::find($cartItemId);
        if (!$item) {
            return ['status' => 'error', "message" => "Item not found"];
        }
        $item->update([
            'quantity' => $quantity
        ]);

        return "Item updated successfully";
    }
}
