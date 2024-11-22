<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralDiscount extends Model
{
    public $translatable = ['name'];

    use HasFactory;
    protected $fillable = [
        'name',
        'value',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function foods()
    {
        return $this->belongsToMany(Food::class, 'food_general_discount');
    }
}
