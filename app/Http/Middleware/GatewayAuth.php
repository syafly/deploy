<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GatewayAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->bearerToken();

        if (!$apiKey || !hash_equals(config('auth.my_api_secret'), $apiKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}