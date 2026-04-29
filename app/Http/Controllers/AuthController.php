<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Services\APIGateway;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;
    protected $apiGateway;
    protected $tokenManager;

    public function __construct(
        AuthService $authService,
        APIGateway $apiGateway,
        TokenManager $tokenManager
    ) {
        $this->authService = $authService;
        $this->apiGateway = $apiGateway;
        $this->tokenManager = $tokenManager;
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home'); // Arahkan pengguna ke halaman utama jika sudah login
        }
        return view('login');
    }

    public function login(LoginRequest $request)
    {
        try {
            $tokenData = $this->authService->attemptLogin($request->validated());
            if (!$tokenData) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (ApiGatewayException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        $cookie = Cookie::make('refresh_token', $tokenData['refresh_token'], 60*24*7, '/', null, false, true);
        
        return response()->json([
            'access_token' => $tokenData['access_token'],
            'expires_in' => $tokenData['expires_in'],
        ])->cookie($cookie);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');
        if (!$refreshToken) {
            return response()->json(['error' => 'Refresh token not found'], 401);
        }

        try {
            $newTokens = $this->apiGateway->refreshToken($refreshToken);
        } catch (\App\Exceptions\ApiGatewayException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        // Update session via TokenManager
        $this->tokenManager->refreshTokens($newTokens, $newTokens['expires_in']);

        $cookie = Cookie::make(
            'refresh_token',
            $newTokens['refresh_token'],
            60 * 24 * 7,
            '/',
            null,
            false,
            true
        );

        return response()->json([
            'access_token' => $newTokens['access_token'],
            'expires_in' => $newTokens['expires_in'],
        ])->cookie($cookie);
    }

    public function logout(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');
        if ($refreshToken) {
            $this->apiGateway->revokeToken($refreshToken);
        }

        $this->tokenManager->clearTokens();
        Auth::logout();

        $cookie = Cookie::make('refresh_token', '', -1, '/', null, false, true);
        return redirect('/login')->cookie($cookie);
    }
}