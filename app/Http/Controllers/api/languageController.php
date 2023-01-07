<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Language as LanguageMiddleware;
use App\Http\Resources\LanguageResource;
use App\Models\BusinessLanguage;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Message;
use App\Models\Plan;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index','show']);
        $this->middleware('auth.type:user')->except(['index','show']);
        $this->middleware('role:admin')->except(['index','show']);
    }

    public function index()
    {
        $languages=Language::withTranslations()->get();
        return LanguageResource::collection($languages);
    }

    public function store(Request $request)
    {
        $request->validate( [
            'code'   => ['required', 'string' , 'unique:languages'],
        ]);
        LanguageMiddleware::$all_languages[] = $request->code;
        $request->validate( [
            'title'  => ['required', LanguageMiddleware::rule()],
        ]);

        $language = Language::createWithTranslations([
            'code'  => $request->code,
            'title' => $request->title,
        ]);

        LanguageController::add_all_models_translations_for($language->code);

        return response()->json(new LanguageResource($language), 200);
    }

    public function show(Language $language)
    {
        $language->loadTranslations();
        return response()->json(new LanguageResource($language), 200);
    }

    public function update(Request $request, Language $language)
    {
        $request->validate([
            'code'   => ['string' , Rule::unique('languages', 'code')->ignore($language->id, 'id')],
            'title'  => ['required', LanguageMiddleware::rule()],
        ]);

        $code = $language->code;
        $language->updateWithTranslations([
            'code'  => $request->code,
            'title' => $request->title,
        ]);

        if($language->code != $code)
            Translation::where('language', $code)->update([
                'language' => $language->code,
            ]);

        return response()->json(new LanguageResource($language), 200);
    }

    public function destroy(Language $language)
    {
        BusinessLanguage::where('language_id',$language->id)->delete();
        LanguageController::delete_all_models_translations_for($language->code);
        $language->deleteWithTranslations();
        return response()->json(null, 204);
    }

    public static function add_all_models_translations_for($new_language_code)
    {
        Language::addTranslationsForNewLanguage($new_language_code);
        Country::addTranslationsForNewLanguage($new_language_code);
        City::addTranslationsForNewLanguage($new_language_code);
        Currency::addTranslationsForNewLanguage($new_language_code);
        Plan::addTranslationsForNewLanguage($new_language_code);
        Message::addTranslationsForNewLanguage($new_language_code);
    }

    public static function delete_all_models_translations_for($new_language_code)
    {
        Translation::where('language', $new_language_code)->delete();
    }
}

