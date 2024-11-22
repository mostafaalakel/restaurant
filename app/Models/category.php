<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    use HasFactory;
    public $translatable = ['name'];

    protected $fillable = [
        'name'
    ];

    public function foods(){
        return $this->hasMany(Food::class);
    }
}
