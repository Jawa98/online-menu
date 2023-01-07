<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessLanguage extends Model
{
    use HasFactory;

    protected $table = 'business_language';

    protected $fillable= [
        'business_id',
        'language_id',
    ];
}

