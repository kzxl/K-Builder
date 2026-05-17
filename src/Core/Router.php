<?php

declare(strict_types=1);

namespace KBuilder\Core;

use KBuilder\Http\Controllers\AuthController;
use KBuilder\Http\Controllers\PublicController;
use KBuilder\Http\Controllers\Api\ComponentController;
use KBuilder\Http\Controllers\Api\PageController;
use KBuilder\Http\Controllers\Api\PostController;
use KBuilder\Http\Controllers\Api\TaxonomyController;
use KBuilder\Http\Controllers\Api\SiteController;
use KBuilder\Http\Controllers\Api\MediaController;
use KBuilder\Http\Controllers\Api\AdminMenuController;
use KBuilder\Http\Controllers\Api\MenuController;
use KBuilder\Http\Controllers\Api\PluginController;
use KBuilder\Http\Controllers\Api\SettingsController;
use KBuilder\Http\Middleware\JwtMiddleware;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Router
{
    public function __construct(private readonly ContainerInterface $container) {}

    public function register(App $app): void
    {
        $jwt = new JwtMiddleware($this->container);

        // ── Public frontend routes ──────────────────────────────────────
        $app->get('/', [PublicController::class, 'home']);
        $app->get('/sitemap.xml', [PublicController::class, 'sitemap']);

        // ── Auth ────────────────────────────────────────────────────────
        $app->group('/api', function (RouteCollectorProxy $api) {
            $api->post('/auth/login', [AuthController::class, 'login']);
            $api->post('/auth/logout', [AuthController::class, 'logout']);
            $api->get('/auth/me', [AuthController::class, 'me']);
        });

        // ── Protected API ───────────────────────────────────────────────
        $app->group('/api', function (RouteCollectorProxy $g) {

            // Sites
            $g->get('/sites', [SiteController::class, 'index']);
            $g->post('/sites', [SiteController::class, 'store']);
            $g->get('/sites/{id}', [SiteController::class, 'show']);
            $g->put('/sites/{id}', [SiteController::class, 'update']);
            $g->delete('/sites/{id}', [SiteController::class, 'destroy']);

            // Dashboard
            $g->get('/dashboard/stats', [\KBuilder\Http\Controllers\Api\DashboardController::class, 'stats']);

            // Pages
            $g->get('/pages', [PageController::class, 'index']);
            $g->post('/pages', [PageController::class, 'store']);
            $g->get('/pages/{id}', [PageController::class, 'show']);
            $g->put('/pages/{id}', [PageController::class, 'update']);
            $g->post('/pages/{id}/publish', [PageController::class, 'publish']);
            $g->post('/pages/{id}/duplicate', [PageController::class, 'duplicate']);
            $g->get('/pages/{id}/revisions', [PageController::class, 'revisions']);
            $g->post('/pages/{id}/revisions/{revId}/restore', [PageController::class, 'restoreRevision']);
            $g->delete('/pages/{id}', [PageController::class, 'destroy']);

            // Posts API
            $g->get('/posts', [PostController::class, 'index']);
            $g->post('/posts', [PostController::class, 'store']);
            $g->get('/posts/{id}', [PostController::class, 'show']);
            $g->put('/posts/{id}', [PostController::class, 'update']);
            $g->delete('/posts/{id}', [PostController::class, 'destroy']);

            // Taxonomies API
            $g->get('/taxonomies', [TaxonomyController::class, 'index']);
            $g->post('/taxonomies', [TaxonomyController::class, 'store']);
            $g->put('/taxonomies/{id}', [TaxonomyController::class, 'update']);
            $g->delete('/taxonomies/{id}', [TaxonomyController::class, 'destroy']);

            // Components (Section types)
            $g->get('/components', [ComponentController::class, 'index']);
            $g->post('/components/preview', [ComponentController::class, 'preview']);

            // Media
            $g->get('/media', [MediaController::class, 'index']);
            $g->post('/media/upload', [MediaController::class, 'upload']);
            $g->delete('/media/{id}', [MediaController::class, 'destroy']);

            // Plugins
            $g->get('/plugins', [PluginController::class, 'index']);
            $g->post('/plugins/{id}/toggle', [PluginController::class, 'toggle']);

            // Settings & Tools
            $g->get('/settings/{group}', [SettingsController::class, 'getGroup']);
            $g->put('/settings/{group}', [SettingsController::class, 'updateGroup']);
            $g->post('/settings/tools/demo', [SettingsController::class, 'seedDemoData']);
            $g->get('/settings/tools/export', [SettingsController::class, 'exportSite']);
            $g->post('/settings/tools/import', [SettingsController::class, 'importSite']);

            // Admin menu (sidebar for React)
            $g->get('/admin/menus', [AdminMenuController::class, 'index']);

            // Site Menus (Navigation)
            $g->get('/menus', [MenuController::class, 'index']);
            $g->post('/menus', [MenuController::class, 'store']);
            $g->get('/menus/{id}', [MenuController::class, 'show']);
            $g->put('/menus/{id}', [MenuController::class, 'update']);
            $g->delete('/menus/{id}', [MenuController::class, 'destroy']);

        })->add($jwt);

        // ── Admin SPA ───────────────────────────────────────────────────
        // React handles routing client-side — serve index.html
        $app->get('/admin[/{path:.*}]', [PublicController::class, 'adminSpa']);

        // ── Public frontend catch-all ───────────────────────────────────
        // Phải nằm dưới cùng để không shadow các route tĩnh (như /api)
        $app->get('/{slug:[a-z0-9\-\/]+}', [PublicController::class, 'page']);
    }
}
