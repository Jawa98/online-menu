<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Middleware\Language as LanguageMiddleware;
use App\Models\Country;
use App\Models\Translation;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Language;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index','show']);
        $this->middleware('auth.type:user')->except(['index','show']);
        $this->middleware('role:admin')->except(['index','show']);
    }
   
    public function index()
    {
        $countries=Country::withTranslations()->get();
        return CountryResource::collection($countries);
    }

    public function store(Request $request)
    {
        $request->validate( [
            'code'  => ['required', 'string', 'unique:countries'],
            'name'  => ['required', 'array', LanguageMiddleware::rule()],
        ]);

        $country = Country::createWithTranslations([
            'code' => $request->code,
            'name' => $request->name,
        ]);

        return response()->json(new CountryResource($country), 200);
    }

    public function show(Country $country)
    {
        $country->loadTranslations();
        return response()->json(new CountryResource($country), 200);
    }

    public function update(Request $request, Country $country)
    {
        $request->validate([
            'code'   => ['required','string' , Rule::unique('countries', 'code')->ignore($country->id, 'id')],
            'name'   => ['required', LanguageMiddleware::rule()],
        ]);

        $country->updateWithTranslations([
            'code'  => $request->code,
            'name'  => $request->name,
        ]);

        return response()->json(new CountryResource($country), 200);
    }

    public function destroy(Country $country)
    {
        $country->deleteWithTranslations();
        return response()->json(null, 204);
    }
}

