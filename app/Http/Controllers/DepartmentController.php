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
    public function index()
    {
        $departments = Department::orderbydesc('id')->get();
        return apiResponse(true, 'Operation completed successfully', $departments, 200);
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
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $department = Department::create([
            'name' => $request->name
        ]);

        $assignQa = UserDepartment::create([
            'department_id' => $department->id,
            'user_id' => $request->user_id,
        ]);
        
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
        UserDepartment::where('department_id',$department->id)->delete();
        $department->delete();

        return apiResponse(true, 'Operation completed successfully', [], 200);
    }
}
