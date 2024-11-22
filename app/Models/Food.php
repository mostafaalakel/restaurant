<?php

namespace App\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Food extends Model
{
    use HasFactory;
    protected $table = 'foods';
    public $translatable = ['name', 'description'];
    protected $fillable = [
        'name',
        'category_id',
        'price',
        'image',
        'quantity',
        'description'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function generalDiscounts()
    {
        return $this->belongsToMany(GeneralDiscount::class, 'food_general_discount');
    }

    public function codeDiscounts()
    {
        return $this->belongsToMany(CodeDiscount::class, 'food_code_discount');
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating');
    }
}
