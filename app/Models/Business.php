<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
   use HasFactory, HasTranslation;

    protected $fillable = [
        'code',
        'country_id',
        'city_id',
        'api_key',
        'subscription_at',
        'expire_at',
        'plan_id',
    ];

    protected $translated_columns = [
        'name',
        'address',
    ];

    public function owners(){
        return  $this->belongsToMany(User::class, 'business_user', 'business_id', 'user_id');
    }

    public function languages(){
        return  $this->belongsToMany(Language::class, 'business_language', 'business_id', 'language_id');
    }
}

