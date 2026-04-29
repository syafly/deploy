<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait RestResponse
{
    protected function successResponse($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $status);
    }

    protected function errorResponse(string $message, int $status = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }

    protected function htmlPaginatedResponse(string $html, ?bool $hasMore = null, ?int $count = null, ?int $total = null, ?string $message = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'html' => $html,
            'hasMore' => $hasMore,
            'count' => $count,
            'total' => $total,
            'message' => $message
        ]);
    }
}