<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Http\Middleware\Language as LanguageMiddleware;
use App\Models\Currency;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index','show']);
        $this->middleware('auth.type:user')->except(['index','show']);
        $this->middleware('role:admin')->except(['index','show']);
    }
   
    public function index()
    {
        $currencies = Currency::withTranslations()->get();
        return CurrencyResource::collection($currencies);
    }

    public function store(Request $request)
    {
        $request->validate( [
            'code'  => ['required', 'string', 'unique:currencies'],
            'name'  => ['required', 'array', LanguageMiddleware::rule()],
        ]);

         $currency = Currency::createWithTranslations([
            'code' => $request->code,
            'name' => $request->name,
        ]);

        return response()->json(new CurrencyResource($currency), 200);
    }

    public function show(Currency $currency)
    {
        $currency->loadTranslations();
        return response()->json(new CurrencyResource($currency), 200);
    }

    public function update(Request $request, Currency $currency)
    {
        $request->validate([
            'code'   => ['required','string' , Rule::unique('currencies', 'code')->ignore($currency->id, 'id')],
            'name'   => ['required', LanguageMiddleware::rule()],
        ]);

        $currency->updateWithTranslations([
            'code'  => $request->code,
            'name'  => $request->name,
        ]);

        return response()->json(new CurrencyResource($currency), 200);
    
    }

    public function destroy(Currency $currency)
    {
        $currency->deleteWithTranslations();
        return response()->json(null, 204);
    }
}

