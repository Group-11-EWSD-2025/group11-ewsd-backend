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