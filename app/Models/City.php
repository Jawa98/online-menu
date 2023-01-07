<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'code',
        'country_id',
    ];

    protected $translated_columns = [
        'name',
    ];
}

