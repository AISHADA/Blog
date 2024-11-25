<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request): Application|Response|ResponseFactory
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required'
        ]);

        $validate['password'] = bcrypt($validate['password']);

        //$image= 

        $user = User::create($validate);

        return response([
            'user' => $user,
            'token' => $user->createToken('secret')->plainTextToken
        ], 200);
    }


    public function login(Request $request): Application|Response|ResponseFactory
    {
        $validate = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if (!Auth::attempt($validate)) {
            return response([
                'message' => 'Invalid credentials.'
            ], 403);
        }

        $user = User::where('email', $request->email)->first();

        $tokens = DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->id)
            ->get();

        if (count($tokens) > 0) {
                DB::table('personal_access_tokens')
                ->where('tokenable_id', $user->id)
                ->delete();
        }

        $user = Auth::user();

        if ($user instanceof User) {
            $accesstoken = $user->createToken('secret')->plainTextToken;
            return response([
                'user' => $user,
                'token' => $accesstoken
            ], 200);
        }

        return response([
            'message' => 'Unable to create token. User is not authenticated or invalid.'
        ], 500);
    }

    public function logout(): Application|Response|ResponseFactory
    {
        $user = auth::user();
        if($user instanceof User){
            $user->tokens()->delete();
        }
        return response([
            'message' => 'logout Done..'
        ], 200);
    }
}
