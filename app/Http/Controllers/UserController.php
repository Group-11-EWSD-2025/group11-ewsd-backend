<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDepartment;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function index(Request $request)
    {
        $paginate = $request->per_page ?? 10;
        $query    = User::query()->with('departments');

        // Filter by department_id if provided (via pivot table)
        if ($request->has('department_id')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')->orWhere('email', 'like', '%' . $request->search . '%');
        }

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

                                                            // Apply sorting
        $orderBy = $request->get('order_by', 'created_at'); // default column
        $sortBy  = $request->get('sort_by', 'desc');        // default direction

        $query->orderBy($orderBy, $sortBy);

        $users = $query->paginate($paginate);

        return apiResponse(true, 'Operation completed successfully', $users, 200);
    }

    public function store(Request $request)
    {
        $request->merge([
            'department_id' => is_array($request->department_id) ? $request->department_id : explode(',', $request->department_id),
        ]);

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'email'         => 'required|email|unique:users',
            'phone'         => 'required',
            'role'          => 'required',
            'department_id' => 'array|required|exists:departments,id',
            'password'      => 'required',
            'profile'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        try {
            if ($request->hasFile('profile')) {
                $image     = $request->file('profile');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
            }

            $plainPassword = $request->password; // Store original password for email

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'role'     => $request->role,
                'password' => Hash::make($plainPassword),
                'profile'  => $imageName ?? null,
            ]);

            foreach ($request->department_id as $department_id) {
                UserDepartment::create([
                    'department_id' => $department_id,
                    'user_id'       => $user->id,
                ]);
            }

            // Send welcome email with credentials
            $emailSent = $this->mailService->sendNewUserAccountMail(
                $user,
                $plainPassword,
                auth()->user()
            );

            if (! $emailSent) {
                Log::warning('Failed to send welcome email to new user', [
                    'user_id' => $user->id,
                ]);
            }

            return apiResponse(true, 'Operation completed successfully', $user, 200);
        } catch (\Exception $e) {
            return apiResponse(false, 'Failed to create user', null, 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return apiResponse(true, 'Operation completed successfully', $user, 200);
        } catch (\Exception $e) {
            return apiResponse(false, 'User not found', null, 404);
        }
    }

    public function update(Request $request)
    {
        $request->merge([
            'department_id' => is_array($request->department_id) ? $request->department_id : explode(',', $request->department_id),
        ]);

        $validator = Validator::make($request->all(), [
            'id'            => 'required|exists:users,id',
            'name'          => 'required',
            'email'         => 'required|email',
            'phone'         => 'required',
            'role'          => 'required',
            'department_id' => 'array|required',
            'profile'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $user = User::findOrFail($request->id);

        if ($request->hasFile('profile')) {
            $image     = $request->file('profile');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
        }

        $user->update([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'role'    => $request->role,
            'profile' => $imageName ?? $user->profile,
        ]);

        // Update departments
        UserDepartment::where('user_id', $user->id)->delete();
        foreach ($request->department_id as $department_id) {
            UserDepartment::create([
                'department_id' => $department_id,
                'user_id'       => $user->id,
            ]);
        }
        return apiResponse(true, 'Operation completed successfully', $user, 200);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }
        $user = User::findOrFail($request->id);
        $user->delete();

        return apiResponse(true, 'Operation completed successfully', null, 200);
    }

    public function activityLogs(Request $request)
    {
        $user         = User::findOrFail($request->id);
        $activityLogs = $user->activityLogs->map(function ($log) {
            return [
                'id'         => $log->id,
                'ip_address' => $log->ip_address,
                'login_at'   => Carbon::parse($log->created_at)->diffForHumans(),
            ];
        });
        return apiResponse(true, 'Operation completed successfully', $activityLogs, 200);
    }
}
