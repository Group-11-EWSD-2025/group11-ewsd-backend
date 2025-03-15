<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Auth;

class AuthController extends Controller
{
    // User Registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);
        $user->token = $token;
        return apiResponse(true, 'Register success', $user, 201);
    }

    public function me(){
        $user = Auth::user();
        return apiResponse(true, 'Operation completed successfully', $user, 201);
    }

    // User Login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return apiResponse(false, 'Invalid credentials', [], 401);
        }
        $user = User::where('email',$request->email)->first();
        $user->token = $token;
        return apiResponse(true, 'Log in success', $user, 201);
    }

    // User Logout
    public function logout()
    {
        auth()->logout();
        return apiResponse(true, 'Successfully logged out', [], 201);
    }
}
