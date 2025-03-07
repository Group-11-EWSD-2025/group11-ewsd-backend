<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ($token = JWTAuth::attempt($credentials)) {
            $user = auth()->user();
            $user->token = $token;
            return apiResponse(true, 'Login Success', $user, 200);
        }

        return apiResponse(false, 'Unauthorized', [], 401);
    }

    public function me()
    {   
        $user = auth()->user();
        return apiResponse(true, 'Operation Success', $user, 200);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
    // Register Method
    public function register(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6', // 'confirmed' ensures 'password_confirmation' field is provided
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return apiResponse(false, 'Register Failed', [], 400);
        }

        // Create a new user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password), // Encrypt the password
        ]);

        // Generate JWT token for the new user
        $token = JWTAuth::fromUser($user);
        $user->token = $token;
        return apiResponse(true, 'Register Success', $user, 200);
    }
}
