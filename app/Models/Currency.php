<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'code',
    ];

    protected $translated_columns = [
        'name',
    ];
}


