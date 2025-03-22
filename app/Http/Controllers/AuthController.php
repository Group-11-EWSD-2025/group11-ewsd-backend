<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Auth;
use App\Helpers\ActivityLogger;
use App\Services\MailService;
class AuthController extends Controller
{
    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

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

        $user = auth()->user();
        ActivityLogger::logLogin($user->id);

        return apiResponse(true, 'Login successful', [
            'token' => $token,
            'user' => $user
        ], 200);
    }

    // User Logout
    public function logout()
    {
        try {
            auth()->logout();
            return apiResponse(true, 'Successfully logged out', null, 200);
        } catch (\Exception $e) {
            return apiResponse(false, 'Logout failed', null, 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        try {
            $user = User::where('email', $request->email)->first();
            $newPassword = Str::random(10);

            $user->password = Hash::make($newPassword);
            $user->save();

            $this->mailService->sendForgotPasswordMail($user, $newPassword);

            return apiResponse(true, 'Password reset email has been sent successfully', null, 200);
        } catch (\Exception $e) {
            return apiResponse(false, 'Failed to send password reset email', null, 500);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|different:current_password',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        try {
            $user = auth()->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return apiResponse(false, 'Current password is incorrect', null, 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();
            return apiResponse(true, 'Password changed successfully', null, 200);
        } catch (\Exception $e) {
            return apiResponse(false, 'Failed to change password', null, 500);
        }
    }
}
