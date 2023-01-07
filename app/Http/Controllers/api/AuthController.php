<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['logout']);
        $this->middleware('auth.type:user')->only(['logout']);
    }

    public function login(Request $request)
    {
        $request->validate( [
            'username' => 'required',
            'password' => ['required','min:6']
        ]);

        if (Auth::attempt($request->all())) {
            $user = to_user(Auth::user());
            $token = $user->createToken('Sanctum', [])->plainTextToken;
            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
            ], 200);
        }

        return response()->json([
            'message' => ___('auth.failed'),
            'errors' => [
                'username' => [___('auth.failed')]
            ]
        ], 422);
    }

    public function logout(){
        $user =to_user(Auth::user());
        $user->tokens()->delete();
    }

    public function check_username(Request $request)
    {
        $request->validate([
            'username' => ['required', 'regex:/^[a-z0-9\.]{4,}$/', 'unique:users'],
        ]);
    }
}

