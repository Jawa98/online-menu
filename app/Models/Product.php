<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'code',
        'price',
        'business_id',
        'category_id',
        'currency_id',
        'available',
        'sort',
      ];

    protected $translated_columns = [
        'name',
        'description',
      ]; 

    public function offers(){
      return $this->hasMany(Offer::class);
    }

    public function media(){
      return $this->hasMany(ProductMedia::class);
    }
}

