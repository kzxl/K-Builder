<?php

declare(strict_types=1);

namespace KBuilder\Http\Middleware;

use KBuilder\Core\Cache\CacheManager;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Chặn truy cập route nếu user không có permission yêu cầu.
 *
 * Phải chạy SAU JwtMiddleware (cần các attribute auth_roles / auth_user_id).
 * Role "super_admin" luôn được bỏ qua mọi kiểm tra.
 *
 * Cách dùng:
 *   $route->add(new RequirePermission($container, 'plugins.toggle'));
 */
class RequirePermission implements MiddlewareInterface
{
    /** @var string[] */
    private array $required;

    /**
     * @param string|string[] $permission Một hoặc nhiều permission slug (resource.action).
     *                                     User chỉ cần có MỘT trong số đó là đủ.
     */
    public function __construct(
        private readonly ContainerInterface $container,
        string|array $permission,
    ) {
        $this->required = (array) $permission;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $roles = $request->getAttribute('auth_roles') ?? [];
        $roles = is_array($roles) ? $roles : (array) $roles;

        // Super admin toàn quyền
        if (in_array('super_admin', $roles, true)) {
            return $handler->handle($request);
        }

        if (empty($roles)) {
            return $this->forbidden('Tài khoản chưa được gán vai trò');
        }

        $granted = $this->resolvePermissions($roles);

        foreach ($this->required as $perm) {
            if (in_array($perm, $granted, true)) {
                return $handler->handle($request);
            }
        }

        return $this->forbidden('Bạn không có quyền thực hiện hành động này');
    }

    /**
     * Lấy danh sách permission slug của tập role (có cache để tránh query mỗi request).
     *
     * @param string[] $roleSlugs
     * @return string[]
     */
    private function resolvePermissions(array $roleSlugs): array
    {
        sort($roleSlugs);
        $cacheKey = 'perms:' . implode(',', $roleSlugs);

        $resolver = function () use ($roleSlugs): array {
            return DB::table('role_permissions as rp')
                ->join('roles as r', 'r.id', '=', 'rp.role_id')
                ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
                ->whereIn('r.slug', $roleSlugs)
                ->pluck('p.slug')
                ->unique()
                ->values()
                ->toArray();
        };

        try {
            /** @var CacheManager $cache */
            $cache = $this->container->get(CacheManager::class);
            return $cache->remember($cacheKey, $resolver, 300);
        } catch (\Throwable $e) {
            // Cache lỗi thì query trực tiếp, không chặn người dùng
            return $resolver();
        }
    }

    private function forbidden(string $message): ResponseInterface
    {
        $response = new Response(403);
        $response->getBody()->write(json_encode([
            'success' => false,
            'error'   => $message,
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
