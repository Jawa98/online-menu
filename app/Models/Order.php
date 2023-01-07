<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'customer_id',
        'business_id',
        'address',
        'mobile',
        'total_price',
        'currency_id'
    ];

    public function items(){
        return $this->belongsToMany(Product::class, 'product_orders', 'order_id', 'product_id')->withPivot('quantity', 'price', 'currency_id');
    }
}


