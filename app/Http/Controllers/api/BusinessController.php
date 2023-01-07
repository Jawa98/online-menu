<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Http\Middleware\Language as LanguageMiddleware;
use App\Http\Resources\LanguageResource;
use App\Http\Resources\UserResource;
use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\Language;
use App\Models\Product;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('auth.type:user');
        $this->middleware('role:admin');
    }

     public function index()
    {
        $businesses=Business::all();
        return BusinessResource::collection($businesses);
    }

    public function store(Request $request)
    {
        $request->validate( [
            'code'            => ['required', 'string', 'unique:businesses'],
            'subscription_at' => ['date'],
            'expire_at'       => ['date'],
            'city_id'         => ['required', 'exists:cities,id'],
            'languages'       => ['required', 'array', 'exists:languages,code'],
            'plan_id'         => ['exists:plans,id'],
        ]);

        $business_languages = $request->languages;

        $request->validate( [
            'name'     => ['required', LanguageMiddleware::rule($business_languages)],
            'address'  => ['required', LanguageMiddleware::rule($business_languages)],
        ]);

        $api_key = Str::random(20);
        $country_id = City::where('id',$request->city_id)->first()->country_id;

        $business = Business::createWithTranslations([
            'code'            => $request->code,
            'name'            => $request->name,
            'address'         => $request->address,
            'subscription_at' => $request->subscription_at,
            'expire_at'       => $request->expire_at,
            'country_id'      => $country_id,
            'city_id'         => $request->city_id,
            'api_key'         => $api_key,
            'plan_id'         => $request->plan_id,
        ],null,$business_languages);

        $languages = Language::whereIn('code', $business_languages)->pluck('id')->toArray();
        $business->languages()->attach($languages);

        return response()->json(new BusinessResource($business), 200);
    }

    public function show(Business $business)
    {
        $business->loadTranslations();
        return new BusinessResource($business);
    }

    public function update(Request $request, Business $business)
    {
        $request->validate( [
            'code'            => ['required', 'string', Rule::unique('businesses', 'code')->ignore($business->id, 'id')],
            'name'            => ['required', 'array'],
            'address'         => ['required', 'array'],
            'subscription_at' => ['date'],
            'expire_at'       => ['date'],
            'city_id'         => ['required', 'exists:cities,id'],
            'plan_id'         => ['exists:plans,id'],
        ]);

        $business_languages = $business->languages()->pluck('code')->toArray();

        $request->validate( [
            'name'     => ['required', LanguageMiddleware::rule($business_languages)],
            'address'  => ['required', LanguageMiddleware::rule($business_languages)],
        ]);

        $country_id = City::where('id',$request->city_id)->first()->country_id;

        $business->updateWithTranslations([
            'code'            => $request->code,
            'name'            => $request->name,
            'address'         => $request->address,
            'subscription_at' => $request->subscription_at,
            'expire_at'       => $request->expire_at,
            'country_id'      => $country_id,
            'city_id'         => $request->city_id,
            'plan_id'         => $request->plan_id,
        ], [], $business_languages);

        return response()->json(new BusinessResource($business), 200);
    }

    public function destroy(Business $business)
    {
        $business->deleteWithTranslations();
        return response()->json(null, 204);
    }

    public function get_owners(Business $business){
        return response()->json(UserResource::collection($business->owners), 200);
    }

    public function add_owner(Request $request, Business $business){
        $request->validate( [
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::find($request->user_id);
        if($user->role != "business_owner")
            return response()->json([
                'message' => __('validation.exists', ['attribute' => 'user']),
                'errors' => [
                    'user_id' => [__('validation.exists', ['attribute' => 'user'])]
                ]
            ], 422);

        $exists = $business->owners()->where('user_id',$request->user_id)->exists();
        if(!$exists)
           $business->owners()->attach($request->user_id);
        return response()->json(new UserResource($user), 200);
    }

    public function remove_owner(Business $business,Request $request){
        $request->validate( [
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $business->owners()->detach($request->user_id);
        return response()->json(null, 204);
    }

    public function get_languages(Business $business){
        foreach($business->languages as $lang)
            $lang->loadTranslations();
        return response()->json(LanguageResource::collection($business->languages), 200);
    }

    public function add_language(Request $request, Business $business){
        $request->validate( [
            'language_id'       => ['required', 'exists:languages,id'],
        ]);

        $language = Language::find($request->language_id);
        $language->loadTranslations();

        $exists = $business->languages()->where('language_id',$request->language_id)->exists();
        if(!$exists)
        {
            $business->languages()->attach($request->language_id);
            BusinessController::add_all_models_translations_for($language->code, $business);
        }
        return response()->json(new LanguageResource($language), 200);
    }

    public function remove_language(Business $business,Request $request){
        $request->validate( [
            'language_id'       => ['required', 'exists:languages,id'],
        ]);

        $language = Language::find($request->language_id);
        $business->languages()->detach($request->language_id);

        BusinessController::delete_all_models_translations_for($language->code, $business);
        return response()->json(null, 204);
    }

    public static function add_all_models_translations_for($new_language_code, $business)
    {
        Business::addTranslationsForNewLanguage($new_language_code, ['id' => $business->id]);
        Category::addTranslationsForNewLanguage($new_language_code, ['business_id' => $business->id]);
        Product::addTranslationsForNewLanguage($new_language_code, ['business_id' => $business->id]);
    }

    public static function delete_all_models_translations_for($language_code, $business)
    {
        $business->translations()->where('language', $language_code)->delete();

        $categories_ids = Category::where('business_id',$business->id)->pluck('id')->toArray();
        Translation::where('translation_type',Category::class)->whereIn('translation_id',$categories_ids)->where('language', $language_code)->delete();

        $products_ids = Product::where('business_id',$business->id)->pluck('id')->toArray();
        Translation::where('translation_type',Product::class)->whereIn('translation_id',$products_ids)->where('language', $language_code)->delete();
    }
}


