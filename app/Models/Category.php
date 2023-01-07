<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'code',
        'image',
        'parent_id',
        'business_id',
        'group_category',
        'sort',
      ];

    protected $translated_columns = [
        'name',
      ];  
}



