<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbSecurity;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Hook\HookSystem;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string { return 'kb-security'; }
    public function getName(): string { return 'Security & Rate Limiting'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getType(): string { return 'system'; }
    public function getDescription(): string { return 'Bảo vệ hệ thống bằng security headers và giới hạn số lượng request API (rate limiting).'; }

    public function registerRoutes(App $app): void
    {
        require_once __DIR__ . '/SecurityMiddleware.php';

        $container = $app->getContainer();
        if ($container === null) {
            return;
        }

        // Cho phép tinh chỉnh limit qua biến môi trường
        $apiLimit   = (int) ($_ENV['SECURITY_API_RATE_LIMIT']   ?? 120);
        $loginLimit = (int) ($_ENV['SECURITY_LOGIN_RATE_LIMIT'] ?? 8);
        $window     = (int) ($_ENV['SECURITY_RATE_WINDOW']      ?? 60);

        // Middleware global: áp dụng cho toàn bộ request
        $app->add(new SecurityMiddleware($container, $apiLimit, $loginLimit, $window));
    }

    public function boot(HookSystem $hooks): void
    {
        // Hiện chưa cần hook bổ sung; rate limit + headers nằm ở middleware.
    }
}
