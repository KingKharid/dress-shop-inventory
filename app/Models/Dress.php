<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dress extends Model
{
    protected $fillable = [
    'name',
    'description',
    'quantity', 
    'original_quantity',
    'photo',
    'buying_price',
    'selling_price',
    'is_sold',
];

}
