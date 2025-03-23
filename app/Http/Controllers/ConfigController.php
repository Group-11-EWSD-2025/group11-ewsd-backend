<?php
namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
}
