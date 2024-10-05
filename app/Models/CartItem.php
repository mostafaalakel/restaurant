<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'id' ,
        'cart_id',
        'food_id',
        'quantity'
    ];
    Public function food(){
        return $this->belongsTo(Food::class);
    }
    Public function cart(){
        return $this->belongsTo(Cart::class);
    }

    
}
