<?php
namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\UserDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
{
    if ($request->has('user_id')) {
        $count = UserDepartment::where('user_id', $request->user_id)->count();
        if ($count == 0) {
            $departments = Department::orderByDesc('id')->get();
        }
        $departments = Department::whereHas('users', function ($query) use ($request) {
            $query->where('user_id', $request->user_id);
        })->orderByDesc('id')->get();
    } else {
        $departments = Department::orderByDesc('id')->get();
    }

    $result = [];
    foreach ($departments as $department) {
        $result[] = [
            'id' => $department->id,
            'name' => $department->name,
            'idea_count' => 20, // You can change this if it's dynamic
        ];
    }

    return apiResponse(true, 'Operation completed successfully', $result, 200);
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $department = Department::create([
            'name' => $request->name,
        ]);

        if ($request->user_id) {
            $assignQa = UserDepartment::create([
                'department_id' => $department->id,
                'user_id'       => $request->user_id,
            ]);
        }

        return apiResponse(true, 'Operation completed successfully', $department, 200);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $department = Department::find($request->id);
        UserDepartment::where('department_id', $department->id)->delete();
        $department->delete();

        return apiResponse(true, 'Operation completed successfully', [], 200);
    }

    public function detail($id)
    {
        $department = Department::find($id);
        $department->idea_count = 20;
        return apiResponse(true, 'Operation completed successfully', $department, 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'id' => 'required|exists:departments,id'
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }
        $department = Department::find($request->id);
        $department->update([
            'name' => $request->name,
        ]);

        // if ($request->user_id && $request->old_user_id) {
        //     UserDepartment::where('user_id',$request->old_user_id)->where('department_id',$department->id)->delete();
        //     $assignQa = UserDepartment::create([
        //         'department_id' => $department->id,
        //         'user_id'       => $request->user_id,
        //     ]);
        // }

        return apiResponse(true, 'Operation completed successfully', $department, 200);
    }
}
