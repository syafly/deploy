<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse($data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'isSuccess' => true,
            'data' => $data,
        ], $status);
    }

    protected function errorResponse(string $message, $code = null, int $status = 200): JsonResponse
    {
        
        return response()->json([
            'isSuccess' => false, 
            'error' => ['error_code' => $code, 'message' => $message]
        ], $status);
    }
}