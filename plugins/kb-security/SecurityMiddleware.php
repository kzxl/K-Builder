<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbSecurity;

use KBuilder\Core\Cache\CacheManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Middleware bảo mật:
 *  - Thêm security headers cho mọi response.
 *  - Rate limit theo IP cho các route /api/* (chống brute-force / abuse).
 *  - Siết chặt hơn cho endpoint đăng nhập /api/auth/login.
 *
 * Rate limit dùng CacheManager (file hoặc redis) làm bộ đếm fixed-window.
 */
class SecurityMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly int $apiLimit = 120,     // request / window cho /api
        private readonly int $loginLimit = 8,      // request / window cho login
        private readonly int $window = 60,         // độ dài window (giây)
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $ip   = $this->clientIp($request);

        // Chỉ rate-limit các API call
        if (str_contains($path, '/api/')) {
            $isLogin = str_contains($path, '/api/auth/login');
            $limit   = $isLogin ? $this->loginLimit : $this->apiLimit;
            $bucket  = $isLogin ? 'login' : 'api';

            $retryAfter = $this->hitLimit("rl:{$bucket}:{$ip}", $limit);
            if ($retryAfter !== null) {
                return $this->tooManyRequests($retryAfter);
            }
        }

        $response = $handler->handle($request);

        return $this->withSecurityHeaders($response);
    }

    /**
     * Tăng bộ đếm cho key. Trả về số giây phải chờ nếu vượt limit, ngược lại null.
     */
    private function hitLimit(string $key, int $limit): ?int
    {
        try {
            /** @var CacheManager $cache */
            $cache = $this->container->get(CacheManager::class);
        } catch (\Throwable $e) {
            // Không có cache → bỏ qua rate limit, không chặn người dùng
            return null;
        }

        $count = (int) $cache->get($key, 0);

        if ($count >= $limit) {
            return $this->window;
        }

        $cache->set($key, $count + 1, $this->window);
        return null;
    }

    private function clientIp(ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();
        $forwarded = $request->getHeaderLine('X-Forwarded-For');
        if ($forwarded !== '') {
            return trim(explode(',', $forwarded)[0]);
        }
        return $server['REMOTE_ADDR'] ?? 'unknown';
    }

    private function withSecurityHeaders(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'SAMEORIGIN')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('X-XSS-Protection', '1; mode=block')
            ->withHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    private function tooManyRequests(int $retryAfter): ResponseInterface
    {
        $response = new Response(429);
        $response->getBody()->write(json_encode([
            'success' => false,
            'error'   => 'Quá nhiều yêu cầu. Vui lòng thử lại sau.',
        ], JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Retry-After', (string) $retryAfter);
    }
}
