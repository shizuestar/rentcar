<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken($user->name)->plainTextToken;
            return response()->json([
                'message' => 'Login successful',
                'data' => $user,
                'token' => $token
            ], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request){
        try{
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => "Succesful Logout!"], 200);
        } catch(\Exception $e){
            return response()->json(['message' => "Logout Failed", "data" => $e->getMessage()], 500);
        }
    }
}
