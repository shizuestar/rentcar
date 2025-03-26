<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken($user->name)->plainTextToken;
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => $user,
                'token' => $token
            ], 200);
        }

        return response()->json(['status' => 'error','message' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request){
        try{
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => "Succesful Logout!"], 200);
        } catch(\Exception $e){
            return response()->json(['message' => "Logout Failed", "data" => $e->getMessage()], 500);
        }
    }

    public function checkToken(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        $personalToken = PersonalAccessToken::findToken($token);

        if (!$personalToken) {
            return response()->json(['message' => 'Your Session Login is Expired!'], 401);
        }
        $user = $personalToken->tokenable;

        return response()->json([
            'status' => 200,
            'message' => 'Token is valid',
            'user' => $user->only('id', 'name', 'email'),
        ], 200);
    }
}
