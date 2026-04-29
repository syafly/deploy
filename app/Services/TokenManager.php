<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use App\Exceptions\ApiGatewayException;

class TokenManager
{
    public function setTokens(string $accessToken, string $refreshToken, int $expiresIn): void
    {
        Session::put('access_token', $accessToken);
        Session::put('refresh_token', $refreshToken);
        Session::put('expires_at', time() + $expiresIn);
    }

    public function getAccessToken(): ?string
    {
        return Session::get('access_token');
    }

    public function getRefreshToken(): ?string
    {
        return Session::get('refresh_token');
    }

    public function clearTokens(): void
    {
        Session::forget(['access_token', 'refresh_token', 'expires_at']);
    }

    public function isValid(): bool
    {
        $expiresAt = Session::get('expires_at');
        return $expiresAt && $expiresAt > time();
    }

    public function refreshTokens(array $newTokens, int $expiresIn): void
    {
        $this->setTokens(
            $newTokens['access_token'],
            $newTokens['refresh_token'] ?? Session::get('refresh_token'), // jika refresh token tidak dirotasi
            $expiresIn
        );
    }

    public function getAll(): array
    {
        return [
            'access_token' => $this->getAccessToken(),
            'refresh_token' => $this->getRefreshToken(),
            'expires_at' => Session::get('expires_at'),
        ];
    }
}