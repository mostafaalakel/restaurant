<?php

namespace App\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Food extends Model
{
    use HasFactory , HasTranslations;
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

    public function getCalculatePriceAfterDiscountsAttribute()
    {
        if ($this->generalDiscounts->isNotEmpty()) {
            $price_after_discounts = $this->price;
            $values_discounts = 0;

            foreach ($this->generalDiscounts as $discount) {
                if ($discount->is_active == 1 && $discount->start_date <= now() && $discount->end_date >= now()) {
                    $values_discounts += $discount->value;
                }
            }

            $price_after_discounts -= $price_after_discounts * ($values_discounts / 100);
        }
        return $price_after_discounts;
    }

    public function scopeWithAverageRating(Builder $query)
    {
        $query->withCount([
            'reviews as average_rating' => function ($query) {
                $query->select(DB::raw('coalesce(avg(rating), 0)'));
            }
        ])->orderByDesc('average_rating');
    }

    public function scopeWithGeneralDiscounts(Builder $query)
    {
        $query->with(['generalDiscounts' => function ($query) {
            $query->where('is_active', 1)->where('start_date', '<=', now())->where('end_date', '>=', now());
        }]);
    }
}
