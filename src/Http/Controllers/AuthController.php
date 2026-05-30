<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers;

use KBuilder\Domain\User\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class AuthController
{
    public function __construct(
        private readonly AuthService     $authService,
        private readonly LoggerInterface $logger,
    ) {}

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $email    = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->json($response, ['success' => false, 'error' => 'Email và mật khẩu không được để trống'], 422);
        }

        try {
            $ip   = $request->getServerParams()['REMOTE_ADDR'] ?? null;
            $data = $this->authService->login($email, $password, $ip);
            return $this->json($response, ['success' => true, 'data' => $data]);
        } catch (\RuntimeException $e) {
            $this->logger->warning('Login failed', ['email' => $email, 'reason' => $e->getMessage()]);
            return $this->json($response, ['success' => false, 'error' => $e->getMessage()], (int) $e->getCode() ?: 401);
        }
    }

    public function refresh(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body  = $request->getParsedBody() ?? [];
        $token = $body['refresh_token'] ?? '';

        if (empty($token)) {
            return $this->json($response, ['success' => false, 'error' => 'Refresh token required'], 422);
        }

        try {
            $ip   = $request->getServerParams()['REMOTE_ADDR'] ?? null;
            $data = $this->authService->refresh($token, $ip);
            return $this->json($response, ['success' => true, 'data' => $data]);
        } catch (\RuntimeException $e) {
            return $this->json($response, ['success' => false, 'error' => $e->getMessage()], 401);
        }
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body  = $request->getParsedBody() ?? [];
        $token = $body['refresh_token'] ?? '';

        if (!empty($token)) {
            $this->authService->logout($token);
        }

        return $this->json($response, ['success' => true, 'message' => 'Đăng xuất thành công']);
    }

    /**
     * Trả về thông tin user hiện tại dựa trên JWT (route được bảo vệ bởi JwtMiddleware).
     */
    public function me(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = (int) $request->getAttribute('auth_user_id');

        if (!$userId) {
            return $this->json($response, ['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        try {
            $user = $this->authService->me($userId);
            return $this->json($response, ['success' => true, 'data' => $user]);
        } catch (\RuntimeException $e) {
            return $this->json($response, ['success' => false, 'error' => $e->getMessage()], (int) $e->getCode() ?: 404);
        }
    }

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
