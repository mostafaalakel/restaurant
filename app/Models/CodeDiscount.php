<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'value',
        'start_date',
        'end_date',
        'is_active'
    ];

    public function cartItems()
    {
        return $this->belongsToMany(CartItem::class, 'cart_item_discount_code');
    }

    public function foods()
    {
        return $this->belongsToMany(Food::class, 'food_code_discount');
    }
}
