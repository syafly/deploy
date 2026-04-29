<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Exceptions\ApiGatewayException;
use Illuminate\Support\Facades\Log;

class APIGateway
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $tokenManager;

    public function __construct(TokenManager $tokenManager)
    {
        $this->baseUrl = config('services.nodejs.url');
        $this->clientId = config('services.nodejs.client_id');
        $this->clientSecret = config('services.nodejs.secret_key');
        $this->tokenManager = $tokenManager;
    }

    private function request(string $method, string $path, array $payload = [], array $headers = []): array
    {
        try {
            $url = $this->baseUrl . $path;
            $http = Http::withHeaders($headers);
            $response = $http->{$method}($url, $payload);

            if ($response->failed()) {
                $errorBody = $response->json();
                // Ambil message, jika array gabung dengan koma, fallback ke 'error' field, lalu 'Unknown error'
                $rawMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Unknown error';
                $message = is_array($rawMessage) ? implode(', ', $rawMessage) : (string) $rawMessage;
                $status = $response->status();
                Log::error("HTTP {$method} to {$path} failed", [
                    'status' => $status,
                    'message' => $message,
                    'body' => $errorBody,
                ]);
                throw new ApiGatewayException($message, $status);
            }

            return $response->json();
        } catch (\Exception $e) {
            if ($e instanceof ApiGatewayException) {
                throw $e;
            }
            Log::error("Exception when calling {$method} {$path}", ['message' => $e->getMessage()]);
            throw new ApiGatewayException("Unable to connect to service", 503);
        }
    }
    /**
     * Kirim request dengan token yang sudah ada, otomatis refresh jika expired.
     */
    public function sendWithToken(string $method, string $path, array $data = [], int $retry = 1)
    {
        $accessToken = $this->tokenManager->getAccessToken();
        if (!$accessToken) {
            throw new ApiGatewayException('No access token available', 401);
        }

        try {
            return $this->request($method, $path, $data, [
                'Authorization' => 'Bearer ' . $accessToken,
            ]);
        } catch (ApiGatewayException $e) {
            if ($e->getStatusCode() === 401 && $retry > 0) {
                if ($this->refreshAccessToken()) {
                    return $this->sendWithToken($method, $path, $data, $retry - 1);
                }
            }
            throw $e;
        }
    }

    /**
     * Refresh token menggunakan refresh token yang ada.
     */
    protected function refreshAccessToken(): bool
    {
        $refreshToken = $this->tokenManager->getRefreshToken();
        if (!$refreshToken) {
            return false;
        }

        try {
            $newTokens = $this->refreshToken($refreshToken); // panggil method refreshToken yang sudah ada
            $this->tokenManager->refreshTokens($newTokens, $newTokens['expires_in']);
            return true;
        } catch (\Exception $e) {
            Log::error('Refresh token failed', ['error' => $e->getMessage()]);
            $this->tokenManager->clearTokens();
            return false;
        }
    }

    public function getToken(string $role): array
    {
        return $this->request('post', '/auth/token', [
            'client_id' => $this->clientId,
            'secret_key' => $this->clientSecret,
            'role' => $role,
        ]);
    }

    public function refreshToken(string $refreshToken): array
    {
        return $this->request('post', '/auth/refresh', [
            'refreshToken' => $refreshToken,
        ]);
    }

    public function revokeToken(string $refreshToken): bool
    {
        try {
            $this->request('post', '/auth/logout', [
                'refreshToken' => $refreshToken,
            ]);
            return true;
        } catch (ApiGatewayException $e) {
            return false;
        }
    }
}