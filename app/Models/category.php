<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;


class category extends Model
{
    use HasFactory , HasTranslations;
    public $translatable  = ['name'];

    protected $fillable = [
        'name' ,
        'image'
    ];

    public function foods(){
        return $this->hasMany(Food::class);
    }
}
