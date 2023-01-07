<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;
use Mockery\Matcher\Subset;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('index');
        $this->middleware('auth.type:user')->except(['index']);
        $this->middleware('role:admin')->except('index');
    }

    public function index(Request $request)
    {
        $request->validate([
            'business_id'   => [ 'exists:businesses,id'],
        ]);
        $subscriptions = $request->business_id ? Subscription::where('business_id', $request->business_id)->get(): Subscription::all();
        return SubscriptionResource::collection($subscriptions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'          => ['required', 'in:subscribe,renew,change_plan'],
            'price'         => ['required_if:type,subscribe','required_if:type,change_plan', 'numeric', 'min:0'],
            'business_id'   => ['required', 'exists:businesses,id'],
            'duration'      => ['required_if:type,renew', 'in:month,quarter,year'],
            'plan_id'       => ['required_if:type,subscribe','required_if:type,change_plan', 'exists:plans,id'],
        ]);

        $first_subscription = Subscription::where('business_id',$request->business_id)->first();

        if($request->type =='subscribe'){
            if($first_subscription)
                throw new BadRequestException("Already Subscribed");

            $plan_id = $request->plan_id;
            $business = Business::find($request->business_id);
            $business->subscription_at = Carbon::now();
            $business->expire_at = Carbon::now();
            $business->plan_id = $plan_id;
            $business->save();
        }elseif($request->type =='renew'){
            if(!$first_subscription)
                throw new BadRequestException("Not Subscribed");
            
            $business = Business::find($request->business_id);
            $plan_start = max(Carbon::now(),$business->expire_at);
            $plan_id = $business->plan_id;
            $plan = Plan::find($plan_id);           
            switch($request->duration){
                case('month'):
                    $price = $plan->monthly_price;
                    $expire_at = Carbon::parse($plan_start)->addMonth(); 
                    break;
                case('quarter'):
                    $price = $plan->quarterly_price;
                    $expire_at = Carbon::parse($plan_start)->addQuarter(); 
                    break;
                case('year'):
                    $price = $plan->yearly_price;
                    $expire_at = Carbon::parse($plan_start)->addYear(); 
                    break;
            }
            $business->expire_at = $expire_at;
            $business->save();
        }else{
            if(!$first_subscription)
                throw new BadRequestException("Not Subscribed");
            
            $business = Business::find($request->business_id);
            $plan_id = $request->plan_id;
            if($business->plan_id == $plan_id){
                return response()->json([
                    'message' =>  __('validation.exists', ['attribute' => 'plan']),
                    'errors' => [
                        'plan_id' => [ __('validation.exists', ['attribute' => 'plan'])]
                    ]
                ], 422);
            }
        }

        $subscription = new Subscription([
            'type'         => $request->type,
            'price'        => $request->type =='renew'? $price : $request->price,
            'business_id'  => $request->business_id,
            'plan_id'      => $plan_id,
            'duration'     => $request->type =='renew'? $request->duration: null,                
        ]);
        $subscription->save();

        return response()->json(new SubscriptionResource($subscription), 200);
    }
    
    public function show(Subscription $subscription)
    {
        $subscription = Subscription::find($subscription->id);
        return response()->json(new SubscriptionResource($subscription), 200);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $request->validate([
            'price'         => ['numeric', 'min:0'],
            'plan_id'       => ['exists:plans,id'],
        ]);
        if($subscription->type!="renew")
        {
            $last_subscription_id = Subscription::where('business_id', $subscription->business_id)->max('id');
            if($subscription->id == $last_subscription_id){
                if($request->price)
                    $subscription->price = $request->price;
                if($request->plan_id)
                    $subscription->plan_id = $request->plan_id;
                    dd('sdljjjjjjj');        
                $subscription->save();
            return response()->json(new SubscriptionResource($subscription), 200);
            }
            throw new BadRequestException("you cant update this subscription");
        }
   }

    public function destroy(Subscription $subscription)
    {
        if($subscription->type!="renew")
        {
            $subscription=Subscription::find($subscription->id);
            $subscription->delete();
            return response()->json(null, 204);
        }
        return response()->json(null, 409);
    }
}
