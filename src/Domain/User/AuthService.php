<?php

declare(strict_types=1);

namespace KBuilder\Domain\User;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use RuntimeException;

class AuthService
{
    private array $authConfig;

    public function __construct(private readonly UserRepository $users)
    {
        $this->authConfig = require KB_ROOT . '/config/auth.php';
    }

    /**
     * Login: validate credentials, issue access + refresh token.
     */
    public function login(string $email, string $password, ?string $ip = null): array
    {
        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new RuntimeException('Email hoặc mật khẩu không đúng', 401);
        }

        if ($user['status'] !== 'active') {
            throw new RuntimeException('Tài khoản đã bị khóa', 403);
        }

        $roles = $this->users->getRoles((int) $user['id']);

        $accessToken  = $this->issueAccessToken($user, $roles);
        $refreshToken = $this->issueRefreshToken((int) $user['id'], $ip);

        $this->users->updateLastLogin((int) $user['id']);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => $this->authConfig['expiry'],
            'user'          => [
                'id'     => $user['id'],
                'uuid'   => $user['uuid'],
                'name'   => $user['name'],
                'email'  => $user['email'],
                'avatar' => $user['avatar'],
                'roles'  => $roles,
            ],
        ];
    }

    /**
     * Validate JWT access token, trả về claims.
     */
    public function validateToken(string $tokenString): array
    {
        try {
            $decoded = JWT::decode(
                $tokenString,
                new Key($this->authConfig['secret'], 'HS256')
            );
            return (array) $decoded;
        } catch (\Throwable $e) {
            throw new RuntimeException('Invalid or expired token', 401);
        }
    }

    /**
     * Refresh: validate refresh token, issue new access token.
     */
    public function refresh(string $refreshTokenRaw, ?string $ip = null): array
    {
        $hash   = hash('sha256', $refreshTokenRaw);
        $stored = $this->users->findRefreshToken($hash);

        if (!$stored) {
            throw new RuntimeException('Refresh token không hợp lệ hoặc đã hết hạn', 401);
        }

        $user  = $this->users->findById((int) $stored['user_id']);
        $roles = $this->users->getRoles((int) $user['id']);

        // Rotate refresh token
        $this->users->revokeRefreshToken($hash);
        $newRefresh = $this->issueRefreshToken((int) $user['id'], $ip);

        return [
            'access_token'  => $this->issueAccessToken($user, $roles),
            'refresh_token' => $newRefresh,
            'expires_in'    => $this->authConfig['expiry'],
        ];
    }

    public function logout(string $refreshTokenRaw): void
    {
        $hash = hash('sha256', $refreshTokenRaw);
        $this->users->revokeRefreshToken($hash);
    }

    // ─────────────────────────────────────────────

    private function issueAccessToken(array $user, array $roles): string
    {
        $now    = time();
        $expiry = $now + $this->authConfig['expiry'];

        $payload = [
            'iss'   => $this->authConfig['issuer'],
            'iat'   => $now,
            'exp'   => $expiry,
            'sub'   => (string) $user['id'],
            'email' => $user['email'],
            'roles' => $roles,
        ];

        return JWT::encode($payload, $this->authConfig['secret'], 'HS256');
    }

    private function issueRefreshToken(int $userId, ?string $ip): string
    {
        $raw    = bin2hex(random_bytes(32));
        $hash   = hash('sha256', $raw);
        $expiry = new DateTimeImmutable('+' . $this->authConfig['refresh_expiry'] . ' seconds');

        $this->users->saveRefreshToken($userId, $hash, $expiry, $ip);

        return $raw;
    }
}
