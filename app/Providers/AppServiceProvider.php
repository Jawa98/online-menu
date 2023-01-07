<?php

namespace App\Providers;

use App\Http\Middleware\Language as LanguageMiddleware;
use App\Models\Language;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        JsonResource::withoutWrapping();
        if (Schema::hasTable('languages')){
            $languages = Language::query()->pluck('code')->toArray();
            LanguageMiddleware::$all_languages = $languages;
        }
        else
            LanguageMiddleware::$all_languages = [];
        
        if(!Cache::has('messages'))
            load_messages(LanguageMiddleware::$all_languages);
    }
}
