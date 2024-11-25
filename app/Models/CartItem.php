<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'cart_id',
        'food_id',
        'quantity'
    ];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function generalDiscounts()
    {
        return $this->belongsToMany(GeneralDiscount::class, 'cart_item_discount', 'cart_item_id', 'general_discount_id');
    }

    public function codeDiscounts()
    {
        return $this->belongsToMany(CodeDiscount::class, 'cart_item_code_discount', 'cart_item_id', 'code_discount_id');
    }
}
