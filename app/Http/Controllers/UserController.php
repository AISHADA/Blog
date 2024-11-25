<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function userInfo()
    {
        return response([
            'user' => auth::user()
        ], 200);
    }

    public function update(Request $request){

        $validate = $request->validate([
            'name' => 'required|string',
            'image' => 'required|file|mimes:jpg,bmp,png'
        ]);

        if ($request->hasFile('image')){
            $image = $request->image->getClientOriginalName();
            $path = $request->image->storeAs('users', $image, 'images');
        }
            
        $user = User::where('id', auth::user()->id)->first();
        $user->update([
            'name' => $validate['name'],
            'image' => $path
        ]);
        return response()->json([
            'massage' => 'User updated.. ',
            'new user info:' => $user
        ], 200);
    }
}
