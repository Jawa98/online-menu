<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Language as LanguageMiddleware;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index','show']);
        $this->middleware('auth.type:user')->except(['index','show']);
        $this->middleware('role:admin')->except(['index','show']);
    }
   
    public function index()
    {
        $plans = Plan::withTranslations()->get();
        return PlanResource::collection($plans);
    }

    public function store(Request $request)
    {
        $request->validate( [
            'code'            => ['required', 'string', 'unique:plans'],
            'monthly_price'   => ['required', 'numeric', 'min:0'],
            'quarterly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price'    => ['required', 'numeric', 'min:0'],
            'currency_id'     => ['required', 'exists:currencies,id'],
            'title'           => ['required', 'array', LanguageMiddleware::rule()],
            'description'     => ['required', 'array', LanguageMiddleware::rule()],
        ]);

         $plan = Plan::createWithTranslations([
            'code'            => $request->code,
            'title'           => $request->title,
            'description'     => $request->description,
            'monthly_price'   => $request->monthly_price,
            'quarterly_price' => $request->quarterly_price,
            'yearly_price'    => $request->yearly_price,
            'currency_id'     => $request->currency_id,
        ]);

        return response()->json(new planResource($plan), 200);
    }

    public function show(Plan $plan)
    {
        $plan->loadTranslations();
        return response()->json(new PlanResource($plan), 200);
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'code'            => ['required', 'string', Rule::unique('plans', 'code')->ignore($plan->id, 'id')],
            'monthly_price'   => ['required', 'numeric:min:0'],
            'quarterly_price' => ['required', 'numeric:min:0'],
            'yearly_price'    => ['required', 'numeric:min:0'],
            'currency_id'     => ['required', 'exists:currencies,id'],
            'title'           => ['required', 'array', LanguageMiddleware::rule()],
            'description'     => ['required', 'array', LanguageMiddleware::rule()],
        ]);

        $plan->updateWithTranslations([
            'code'            => $request->code,
            'title'           => $request->title,
            'description'     => $request->description,
            'monthly_price'   => $request->monthly_price,
            'quarterly_price' => $request->quarterly_price,
            'yearly_price'    => $request-> yearly_price,
            'currency_id'     => $request->currency_id,
        ]);

        return response()->json(new PlanResource($plan), 200);
    }

    public function destroy(Plan $plan)
    {
        $plan->deleteWithTranslations();
        return response()->json(null, 204);
    }
}

