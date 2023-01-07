<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Middleware\Language as LanguageMiddleware;
use Illuminate\Validation\Rule;
use App\Models\City;
use App\Models\Translation;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index','show']);
        $this->middleware('auth.type:user')->except(['index','show']);
        $this->middleware('role:admin')->except(['index','show']);
    }
   
    public function index()
    {
        $cities=City::withTranslations()->get();
        return CityResource::collection($cities);
    }

    public function store(Request $request)
    {
        $request->validate( [
            'code'       => ['required', 'string', 'unique:countries'],
            'name'       => ['required', 'array', LanguageMiddleware::rule()],
            'country_id' => ['required', 'exists:countries,id'],
        ]);

        $city = City::createWithTranslations([
            'code'       => $request->code,
            'name'       => $request->name,
            'country_id' => $request->country_id
        ]);

        return response()->json(new CityResource($city), 200);
    }

    public function show(City $city)
    {
        $city->loadTranslations();
        return response()->json(new CityResource($city), 200);
    }

    public function update(Request $request, City $city)
    {
        $request->validate([
            'code'       => ['required','string' , Rule::unique('cities', 'code')->ignore($city->id, 'id')],
            'name'       => ['required', LanguageMiddleware::rule()],
            'country_id' => ['required', 'exists:countries,id'],
        ]);

        $city->updateWithTranslations([
            'code'       => $request->code,
            'name'       => $request->name,
            'country_id' => $request->country_id
        ]);

        return response()->json(new CityResource($city), 200);
    }

    public function destroy(City $city)
    {
        $city->deleteWithTranslations();
        return response()->json(null, 204);
    }
}

