<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'code',
        'monthly_price',
        'quarterly_price',
        'yearly_price',
        'currency_id',
    ];

    protected $translated_columns = [
        'title',
        'description',
    ];
}


