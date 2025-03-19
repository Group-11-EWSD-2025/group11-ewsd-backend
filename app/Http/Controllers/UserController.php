<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDepartment;
use Illuminate\Http\Request;
use Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $paginate = $request->per_page ?? 10;
        $query    = User::query();

        // Filter by department_id if provided (via pivot table)
        if ($request->has('department_id')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate($paginate); // Adjust per page count as needed

        return apiResponse(true, 'Operation completed successfully', $users, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'role' => 'required',
            'department_id' => 'array|required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        foreach($request->department_id as $department_id){
            UserDepartment::create([
                'department_id' => $department_id,
                'user_id' => $user->id
            ]);
        }

        return apiResponse(true, 'Operation completed successfully', $user, 200);
    }
}
