<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    protected $apiGateway;
    protected $tokenManager;

    public function __construct(APIGateway $apiGateway, TokenManager $tokenManager)
    {
        $this->apiGateway = $apiGateway;
        $this->tokenManager = $tokenManager;
    }

    public function attemptLogin(array $credentials): ?array
    {
        $user = $this->validateCredentials($credentials);
        if (!$user) {
            return null;
        }

        try {
            $tokenData = $this->apiGateway->getToken($user->user->role);
        } catch (ApiGatewayException $e) {
            // Log error jika perlu
            throw $e; // atau return null, tergantung kebutuhan
        }

        // Simpan token menggunakan TokenManager
        $this->tokenManager->setTokens(
            $tokenData['access_token'],
            $tokenData['refresh_token'],
            $tokenData['expires_in']
        );

        Auth::login($user);

        return $tokenData;
    }

    private function validateCredentials(array $credentials): ?object
    {
        // Gunakan Auth::once untuk cek kredensial tanpa membuat session
        if (Auth::once($credentials)) {
            return Auth::user();
        }
        return null;
    }
}