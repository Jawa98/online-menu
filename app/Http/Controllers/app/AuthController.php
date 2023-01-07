<?php

namespace App\Http\Controllers\app;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['logout','customer']);
        $this->middleware('auth.type:customer')->only(['logout','customer']);
    }

    public function register(Request $request)
    {
        $request->validate( [
            'name'     => 'required',
            'username' => ['required', 'regex:/^[a-z0-9\.]{4,}$/'],
            'password' => ['required','min:6']
        ]);

        $exists = Customer::where('username',$request->username)->where('business_id',$request->business->id)->exists();
        if($exists)
            return response()->json([
                'message' => ___('validation.unique'),
                'errors' => [
                    'username' => [___('validation.unique')]
                ]
            ], 422);

        $customer = new Customer();
        $customer->name = $request->name;
        $customer->username = $request->username;
        $customer->password = bcrypt($request['password']);
        $customer->business_id = $request->business->id;
        $customer->save();
        
        Auth::login($customer);
        $token = $customer->createToken('Sanctum', [])->plainTextToken;

        return response()->json([
            'customer' => new CustomerResource($customer),
            'token' => $token,
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate( [
            'username' => 'required',
            'password' => ['required','min:6']
        ]);

        $customer = Customer::where('username',$request->username)->where('business_id',$request->business->id)->first();
        if(!$customer || !Hash::check($request->password, $customer->password))
            return response()->json([
                'message' => ___('auth.failed'),
                'errors' => [
                    'username' => [___('auth.failed')]
                ]
            ], 422);
        
        Auth::login($customer);
        $token = $customer->createToken('Sanctum', [])->plainTextToken;

        return response()->json([
            'customer' => new CustomerResource($customer),
            'token' => $token,
        ], 200);
    }

    public function logout(){
        $customer =to_customer(Auth::user());
        $customer->tokens()->delete();
    }

    public function customer(){
        return to_customer(Auth::user());
    }

    public function check_username(Request $request)
    {
        $request->validate([
            'username' => ['required', 'regex:/^[a-z0-9\.]{4,}$/'],
        ]);

        $exists = Customer::where('username',$request->username)->where('business_id',$request->business->id)->exists();
        if($exists)
            return response()->json([
                'message' => ___('validation.unique'),
                'errors' => [
                    'username' => [___('validation.unique')]
                ]
            ], 422);
    }
}
