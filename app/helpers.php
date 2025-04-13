<?php

function apiResponse($status, $message, $data, $statusCode)
{
    return response()->json([
        'meta' => [
            'status'  => $status,
            'message' => $message,
        ],
        'body' => $data,
    ], $statusCode);
}

function getActiveAcademicYear()
{
    return \App\Models\AcademicYear::where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->first();
}