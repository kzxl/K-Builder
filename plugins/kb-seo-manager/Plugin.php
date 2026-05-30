<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbSeoManager;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Admin\AdminMenuRegistry;
use KBuilder\Http\Middleware\JwtMiddleware;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string { return 'kb-seo-manager'; }
    public function getName(): string { return 'SEO Manager'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getType(): string { return 'system'; }
    public function getDescription(): string { return 'Quản lý thẻ Meta, OpenGraph, Sitemap động và Redirect (chuyển hướng URL).'; }

    public function boot(HookSystem $hooks): void
    {
        // Meta/OG/JSON-LD đã được render trong base.twig từ dữ liệu seo của trang.
        // Sitemap.xml và robots.txt do PublicController phụ trách.
    }

    public function registerRoutes(App $app): void
    {
        require_once __DIR__ . '/RedirectMiddleware.php';
        require_once __DIR__ . '/Controllers/RedirectController.php';

        // Middleware redirect: áp dụng toàn cục cho frontend
        $app->add(new RedirectMiddleware());

        $container = $app->getContainer();
        if ($container === null) {
            return;
        }
        $jwt = $container->get(JwtMiddleware::class);

        $app->group('/api/admin/redirects', function ($g) {
            $g->get('', [\KBuilder\Plugins\KbSeoManager\Controllers\RedirectController::class, 'index']);
            $g->post('', [\KBuilder\Plugins\KbSeoManager\Controllers\RedirectController::class, 'store']);
            $g->put('/{id}', [\KBuilder\Plugins\KbSeoManager\Controllers\RedirectController::class, 'update']);
            $g->delete('/{id}', [\KBuilder\Plugins\KbSeoManager\Controllers\RedirectController::class, 'destroy']);
        })->add($jwt);
    }

    public function registerAdminMenus(AdminMenuRegistry $registry): void
    {
        $registry->add(
            id: 'seo-manager',
            label: 'SEO & Redirects',
            icon: 'Search',
            route: '/plugins/kb-seo-manager',
            pluginId: $this->getId(),
            order: 18
        );
    }
}
