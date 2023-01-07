<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'business_id',
        'price',
        'currency_id',
        'expire_at',
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }
}


