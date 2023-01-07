<?php

namespace App\Http\Controllers\app;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Http\Resources\LanguageResource;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function get_business(Request $request){
        return new BusinessResource($request->business);
    }

    public function get_languages(Request $request){
        return LanguageResource::collection($request->app_languages);
    }
}
