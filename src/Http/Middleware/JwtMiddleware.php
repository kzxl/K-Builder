<?php

declare(strict_types=1);

namespace KBuilder\Http\Middleware;

use KBuilder\Domain\User\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Slim\Psr7\Response;

class JwtMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ContainerInterface $container) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Missing or invalid Authorization header');
        }

        $token = substr($authHeader, 7);

        try {
            /** @var AuthService $authService */
            $authService = $this->container->get(AuthService::class);
            $claims = $authService->validateToken($token);

            // Inject user info vào request attributes cho controllers
            $request = $request
                ->withAttribute('auth_user_id', $claims['sub'])
                ->withAttribute('auth_user_email', $claims['email'])
                ->withAttribute('auth_roles', $claims['roles'] ?? [])
                ->withAttribute('auth_site_id', $claims['site_id'] ?? null);

        } catch (\Throwable $e) {
            return $this->unauthorized('Invalid or expired token');
        }

        return $handler->handle($request);
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $response = new Response(401);
        $response->getBody()->write(json_encode([
            'success' => false,
            'error'   => $message,
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
