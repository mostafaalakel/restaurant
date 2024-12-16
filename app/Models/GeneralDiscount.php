<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;


class GeneralDiscount extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['name'];

    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function foods()
    {
        return $this->belongsToMany(Food::class, 'food_general_discount');
    }

    public function cartItems()
    {
        return $this->belongsToMany(CartItem::class, 'cart_item_discount', 'cart_item_id', 'general_discount_id');

    }
}
