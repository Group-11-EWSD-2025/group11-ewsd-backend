<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ActivityLogger;
use App\Helpers\apiResponse;


class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // get all academic years sorted by created_at in descending order

        $academicYears = AcademicYear::orderBy('created_at', 'desc')->get();

        $academicYears = $academicYears->map(function ($academicYear) {
            return [
                'id' => $academicYear->id,
                'start_date' => $academicYear->start_date,
                'end_date' => $academicYear->end_date,
                'idea_submission_deadline' => $academicYear->idea_submission_deadline,
                'final_closure_date' => $academicYear->final_closure_date,
                // if now() is before final_closure_date then status is active else closed
                'status' => now() < $academicYear->final_closure_date ? 'active' : 'closed',
            ];
        });
        return apiResponse(true,"Operation completed successfully", $academicYears, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // check validation
        $validator = Validator::make($request->all(), [
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'idea_submission_deadline' => [
                'required',
                'date',
                'after:start_date',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime(now()->addWeeks(2))) {
                        $fail('The ' . $attribute . ' must be at least 2 weeks from now.');
                    }
                },
            ],
            'final_closure_date' => [
                'required',
                'date',
                'after:idea_submission_deadline',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime(now()->addWeeks(2))) {
                        $fail('The ' . $attribute . ' must be at least 2 weeks from now.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        // create new academic year
        $academicYear = AcademicYear::create([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'idea_submission_deadline' => $request->idea_submission_deadline,
            'final_closure_date' => $request->final_closure_date,
        ]);

        return apiResponse(true, "Operation completed successfully", $academicYear, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AcademicYear  $academicYear
     * @return \Illuminate\Http\Response
     */
    public function detail()
    {
        // get id from request url
        $academicYearId = request()->route('id');

        // get academic year by id
        $academicYear = AcademicYear::find($academicYearId);

        if (!$academicYear) {
            return apiResponse(false, "Academic year not found", null, 404);
        }
        return apiResponse(true, "Operation completed successfully", $academicYear, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AcademicYear  $academicYear
     * @return \Illuminate\Http\Response
     */
    public function edit(AcademicYear $academicYear)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AcademicYear  $academicYear
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        // check validation
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:academic_years,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            // need to be current date + 2 weeks
            'idea_submission_deadline' => [
                'sometimes',
                'date',
                'after:start_date',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime(now()->addWeeks(2))) {
                        $fail('The ' . $attribute . ' must be at least 2 weeks from now.');
                    }
                },
            ],
            'final_closure_date' => [
                'sometimes',
                'date',
                'after:idea_submission_deadline',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime(now()->addWeeks(2))) {
                        $fail('The ' . $attribute . ' must be at least 2 weeks from now.');
                    }
                },
            ],
            'status' => 'sometimes|string',
        ]);
        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        $academicYear = AcademicYear::find($request->id);

        // update academic year
        $academicYear->update([
            'start_date' => $request->start_date ?? $academicYear->start_date,
            'end_date' => $request->end_date ?? $academicYear->end_date,
            'idea_submission_deadline' => $request->idea_submission_deadline ?? $academicYear->idea_submission_deadline,
            'final_closure_date' => $request->final_closure_date ?? $academicYear->final_closure_date,
        ]);
        return apiResponse(true, "Operation completed successfully", $academicYear, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AcademicYear  $academicYear
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // check validation
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:academic_years,id',
        ]);
        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return apiResponse(false, $firstError, null, 400);
        }

        // get id from request url
        $academicYearId = $request->id;

        // get academic year by id
        $academicYear = AcademicYear::find($academicYearId);
        if (!$academicYear) {
            return apiResponse(false, "Academic year not found", null, 404);
        }
        // delete academic year
        $academicYear->delete();
        return apiResponse(true, "Operation completed successfully", null, 200);
    }
}
