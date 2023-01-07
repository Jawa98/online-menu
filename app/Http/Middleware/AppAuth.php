<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AppAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->hasHeader('api_key'))
            $api_key = $request->header('api_key');
        elseif(isset($_SERVER['api_key']))
            $api_key = $_SERVER['api_key'];
        else
            $api_key = "";
        
        $business = Business::where('api_key',$api_key)->first();
        if(!$business)
            throw new BadRequestException('Invalid API key.');
        $business->loadTranslations();
        $request->business = $business;
        $request->app_languages = $business->languages;
        foreach($request->app_languages as $lang)
            $lang->loadTranslations();
        return $next($request);
    } 
}
