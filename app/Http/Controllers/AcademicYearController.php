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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'idea_submission_deadline' => 'sometime|date|after:start_date',
            'final_closure_date' => 'sometime|date|after:idea_submission_deadline',
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
            'status' => "active",
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
            'idea_submission_deadline' => 'sometimes|date|after:start_date',
            'final_closure_date' => 'sometimes|date|after:idea_submission_deadline',
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
            'status' => $request->status ?? $academicYear->status,
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
