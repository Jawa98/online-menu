<?php

namespace App\Http\Controllers\app;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Models\BusinessUser;
use App\Models\Offer;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('auth.type:user')->only(['store', 'update', 'destroy']);
        $this->middleware('role:business_owner')->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $Offers = Offer::with('product')->where('business_id', $request->business->id)
                                        ->where('expire_at', '>=', Carbon::now())
                                        ->get();
        return OfferResource::collection($Offers);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'product_id'       => ['required', 'exists:products,id'],
            'price'            => ['required', 'numeric', 'min:0'],
            'currency_id'      => ['required', 'exists:currencies,id'],
            'expire_at'        => ['required', 'date_format:Y-m-d H:i:s'],
        ]);
        $is_owner = BusinessUser::where('user_id', $user->id)
                                ->where('business_id', $request->business->id)                                
                                ->exists();
        if(!$is_owner)
            return new AuthorizationException();
            
        $offer = new Offer([
            'product_id'  => $request->product_id,
            'business_id' => $request->business->id,
            'price'       => $request->price,
            'currency_id' => $request->currency_id,
            'expire_at'   => $request->expire_at,
        ]);
        $offer->save();
        return response()->json(new OfferResource($offer), 200);
    }

    public function show(Offer $offer)
    {
        return response()->json(new OfferResource($offer), 200);
    }

    public function update(Request $request, Offer $offer)
    {
        $user = Auth::user();
        $request->validate([
            'product_id'       => ['required', 'exists:products,id'],
            'price'            => ['required', 'numeric', 'min:0'],
            'currency_id'      => ['required', 'exists:currencies,id'],
            'expire_at'        => ['required', 'date_format:Y-m-d H:i:s'],
        ]);

        $is_owner = BusinessUser::where('user_id', $user->id)
                                    ->where('business_id', $request->business->id)                                
                                    ->exists();
        if(!$is_owner)
            return new AuthorizationException();

        $offer_data = [
            'product_id'  => $request->product_id,
            'business_id' => $request->business->id,
            'price'       => $request->price,
            'currency_id' => $request->currency_id,
            'expire_at'   => $request->expire_at,
        ];  
        $offer->update($offer_data);
        return response()->json(new OfferResource($offer), 200);
    }

    public function destroy(Request $request, Offer $offer)
    {
        $user = Auth::user();
        $is_owner = BusinessUser::where('user_id', $user->id)
                                ->where('business_id', $request->business->id)                                
                                ->exists();
        if(!$is_owner)
            return new AuthorizationException();

        $offer->delete();
        return response()->json(null, 204);
    }
}
