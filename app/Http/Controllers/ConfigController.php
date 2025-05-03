<?php
namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Idea;
use App\Models\Department;
use App\Models\AcademicYear;

class ConfigController extends Controller
{
    public function index()
    {
        $config = Config::first();

        if (!$config) {
            $defaultConfig = [
                'first_closure_date' => null,
                'final_closure_date' => null
            ];
            return apiResponse(true, 'Config has not been created yet', $defaultConfig, 200);
        }

        $config = [
            'first_closure_date' => $config->first_closure_date,
            'final_closure_date' => $config->final_closure_date
        ];
        return apiResponse(true, 'Operation completed successfully', $config, 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_closure_date' => 'required',
            'final_closure_date' => 'required'
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $config = Config::updateOrCreate(
            [],
            [
                'first_closure_date' => $request->first_closure_date,
                'final_closure_date' => $request->final_closure_date
            ]
        );

        return apiResponse(true, 'Operation completed successfully', [
            'first_closure_date' => $request->first_closure_date,
            'final_closure_date' => $request->final_closure_date
        ], 200);
    }

    public function insight(Request $request){
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'required',
            'department_id' => 'required'
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $total_ideas = Idea::where('academic_year_id', $request->academic_year_id)
            ->where('department_id', $request->deaprtment_id)
            ->count();

        $result = [
            'total_ideas' => $total_ideas,
            'total_comments' => 100,
            'total_users' => 50,
            'most_view_pages' => [
                'Idea Details Page' => 100,
                'User Management Page' => 200,
                'Account Setting Page' => 300
            ],
            'most_active_users' => [
                'User 1' => 10,
                'User 2' => 20,
                'User 3' => 30
            ],
            'browser_usage' => [
                'Chrome' => 50,
                'Firefox' => 30,
                'Safari' => 20,
            ],
        ];
        return apiResponse(true, 'Operation completed successfully', $result, 200);
    }
}
