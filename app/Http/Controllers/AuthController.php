<?php
namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\ActivityLog;
use App\Models\MostViewPage;
use App\Models\User;
use App\Services\MailService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

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
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token       = JWTAuth::fromUser($user);
        $user->token = $token;
        return apiResponse(true, 'Register success', $user, 201);
    }

    public function me()
    {
        $user = Auth::user();
        // last one activity log
        $lastActivityLogs = ActivityLog::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        if (count($lastActivityLogs) > 1) {
            $lastActivityLog     = $lastActivityLogs->first();
            $user->last_login_at = $lastActivityLog ? [
                'created_at' => $lastActivityLog->created_at,
                'ip_address' => $lastActivityLog->ip_address,
                'user_agent' => $lastActivityLog->user_agent,
            ] : null;
        } else {
            $user->last_login_at = null;

        }
        return apiResponse(true, 'Operation completed successfully', $user, 201);
    }

    // User Login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return apiResponse(false, 'Invalid credentials', [], 401);
        }

        $user = auth()->user();
        if ($user->is_disable == 1) {
            return apiResponse(false, 'Your account is disabled', [], 403);
        }
        ActivityLogger::logLogin($user->id);

        return apiResponse(true, 'Login successful', [
            'token' => $token,
            'user'  => $user,
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
            $user        = User::where('email', $request->email)->first();
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
            'new_password'     => 'required|min:8|different:current_password',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        try {
            $user = auth()->user();

            if (! Hash::check($request->current_password, $user->password)) {
                return apiResponse(false, 'Current password is incorrect', null, 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();
            return apiResponse(true, 'Password changed successfully', null, 200);
        } catch (\Exception $e) {
            return apiResponse(false, 'Failed to change password', null, 500);
        }
    }

    public function getRoleList()
    {
        $roles = [
            [
                'value'       => 'admin',
                'label'       => 'Admin',
                'description' => 'Admin role',
            ],
            [
                'value'       => 'qa-manager',
                'label'       => 'QA Manager',
                'description' => 'QA Manager role',
            ],
            [
                'value'       => 'qa-coordinator',
                'label'       => 'QA Coordinator',
                'description' => 'QA Coordinator role',
            ],
            [
                'value'       => 'staff',
                'label'       => 'Staff',
                'description' => 'Staff role',
            ],
        ];

        return apiResponse(true, 'Operation completed successfully', $roles, 200);
    }

    public function requestPasswordReset(Request $request)
    {
        $email = $request->email;
        $user  = User::where('email', $email)->first();
        if (! $user) {
            return apiResponse(false, 'User not authenticated', [], 401);
        }

        // Start DB Transaction
        DB::beginTransaction();

        try {
            // Generate a new random password
            $newPassword = Str::random(10);

            // Update password
            $user->password = Hash::make($newPassword);
            $user->save();

            // Optionally: Cache the new password temporarily for reference (auto-expire in 10 minutes)
            Cache::put("password_reset_{$user->id}", $newPassword, now()->addMinutes(10));

            // Send new password email
            Mail::raw("Your new password is: {$newPassword}", function ($message) use ($email) {
                $message->to($email)
                    ->subject('Your New Password');
            });

            // Commit transaction
            DB::commit();

            return apiResponse(true, 'New password has been sent to your email.', [], 200);

        } catch (\Exception $e) {
            // Rollback if anything fails
            DB::rollBack();

            return apiResponse(false, 'Failed to reset password. Please try again.', ['error' => $e->getMessage()], 500);
        }
    }

    public function storeView(Request $request)
    {
        MostViewPage::updateOrCreate(
            ['page_name' => $request->page_name],
            ['view_count' => DB::raw('view_count + 1')]
        );
        return apiResponse(true, 'Operation Completed', [], 200);
    }

}
