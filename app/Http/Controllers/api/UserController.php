<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('auth.type:user');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $users=User::all();
        return UserResource::collection($users);
    }

    public function store(Request $request)
    {
        $request->validate( [
            'name'     => ['required', 'string'],
            'username' => ['required', 'regex:/^[a-z0-9\.]{4,}$/', 'unique:users'],
            'password' => ['required','min:6'],
            'role'     => ['required', 'in:admin,business_owner'],
        ]);

        $user = new user([
            'name'       => $request->name,
            'username'   => $request->username,
            'password'   => bcrypt($request['password']),
            'role'       => $request->role,
        ]);
        $user->save();

        return response()->json(new UserResource($user), 200);
    }

    public function show(User $user)
    {    
        $user=User::find($user->id);
        return response()->json(new UserResource($user), 200);
    }

    public function update(Request $request, User $user)
    {
        $request->validate( [
            'name'     => 'string',
            'username' => ['regex:/^[a-z0-9\.]{4,}$/', Rule::unique('users', 'username')->ignore($user->id, 'id')],
            'password' => 'min:6',
            'role'     => 'in:admin,business_owner',
        ]);

        $user_data = [
            'name'       => $request->name,
            'username'   => $request->username,
            'password'   => bcrypt($request['password']),
            'role'       => $request->role,
        ];
        $user->update($user_data);

        return response()->json(new UserResource($user), 200);
    }

    public function destroy(User $user)
    {   
        $user=User::find($user->id);
        $user->delete();
    }
}
