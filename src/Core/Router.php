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
use KBuilder\Http\Controllers\Api\ContentTypeController;
use KBuilder\Http\Middleware\JwtMiddleware;
use KBuilder\Http\Middleware\RequirePermission;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Router
{
    public function __construct(private readonly ContainerInterface $container) {}

    public function register(App $app): void
    {
        $jwt = new JwtMiddleware($this->container);
        $perm = fn (string|array $p) => new RequirePermission($this->container, $p);

        // ── Public frontend routes ──────────────────────────────────────
        $app->get('/', [PublicController::class, 'home']);
        $app->get('/sitemap.xml', [PublicController::class, 'sitemap']);
        $app->get('/robots.txt', [PublicController::class, 'robots']);

        // ── Auth ────────────────────────────────────────────────────────
        $app->group('/api', function (RouteCollectorProxy $api) {
            $api->post('/auth/login', [AuthController::class, 'login']);
            $api->post('/auth/refresh', [AuthController::class, 'refresh']);
            $api->post('/auth/logout', [AuthController::class, 'logout']);
        });

        // ── Protected API ───────────────────────────────────────────────
        $app->group('/api', function (RouteCollectorProxy $g) {

            // Current user
            $g->get('/auth/me', [AuthController::class, 'me']);

            // Sites
            $g->get('/sites', [SiteController::class, 'index']);
            $g->post('/sites', [SiteController::class, 'store'])->add($perm('sites.create'));
            $g->get('/sites/{id}', [SiteController::class, 'show']);
            $g->put('/sites/{id}', [SiteController::class, 'update'])->add($perm('sites.edit'));
            $g->delete('/sites/{id}', [SiteController::class, 'destroy'])->add($perm('sites.delete'));

            // Dashboard
            $g->get('/dashboard/stats', [\KBuilder\Http\Controllers\Api\DashboardController::class, 'stats']);

            // Content Types
            $g->get('/content-types', [ContentTypeController::class, 'index']);

            // Pages
            $g->get('/pages', [PageController::class, 'index']);
            $g->post('/pages', [PageController::class, 'store'])->add($perm('pages.create'));
            $g->get('/pages/{id}', [PageController::class, 'show']);
            $g->put('/pages/{id}', [PageController::class, 'update'])->add($perm('pages.edit'));
            $g->post('/pages/{id}/publish', [PageController::class, 'publish'])->add($perm('pages.publish'));
            $g->post('/pages/{id}/duplicate', [PageController::class, 'duplicate'])->add($perm('pages.create'));
            $g->get('/pages/{id}/revisions', [PageController::class, 'revisions']);
            $g->post('/pages/{id}/revisions/{revId}/restore', [PageController::class, 'restoreRevision'])->add($perm('pages.edit'));
            $g->delete('/pages/{id}', [PageController::class, 'destroy'])->add($perm('pages.delete'));

            // Posts API
            $g->get('/posts', [PostController::class, 'index']);
            $g->post('/posts', [PostController::class, 'store']);
            $g->get('/posts/{id}', [PostController::class, 'show']);
            $g->put('/posts/{id}', [PostController::class, 'update']);
            $g->delete('/posts/{id}', [PostController::class, 'destroy']);

            // Taxonomies API
            $g->get('/taxonomies', [TaxonomyController::class, 'index']);
            $g->post('/taxonomies', [TaxonomyController::class, 'store']);
            $g->get('/taxonomies/{id}', [TaxonomyController::class, 'show']);
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
            $g->post('/plugins/install', [PluginController::class, 'install'])->add($perm('plugins.toggle'));
            $g->post('/plugins/{slug}/toggle', [PluginController::class, 'toggle'])->add($perm('plugins.toggle'));
            $g->delete('/plugins/{slug}', [PluginController::class, 'delete'])->add($perm('plugins.toggle'));

            // Settings & Tools
            $g->get('/settings/{group}', [SettingsController::class, 'getGroup']);
            $g->put('/settings/{group}', [SettingsController::class, 'updateGroup'])->add($perm('settings.edit'));
            $g->post('/settings/tools/demo', [SettingsController::class, 'seedDemoData'])->add($perm('settings.edit'));
            $g->get('/settings/tools/export', [SettingsController::class, 'exportSite'])->add($perm('settings.edit'));
            $g->post('/settings/tools/import', [SettingsController::class, 'importSite'])->add($perm('settings.edit'));

            // Admin menu (sidebar for React)
            $g->get('/admin/menus', [AdminMenuController::class, 'index']);

            // Site Menus (Navigation)
            $g->get('/menus', [MenuController::class, 'index']);
            $g->post('/menus', [MenuController::class, 'store']);
            $g->get('/menus/{id}', [MenuController::class, 'show']);
            $g->put('/menus/{id}', [MenuController::class, 'update']);
            $g->delete('/menus/{id}', [MenuController::class, 'destroy']);

            // Users & Roles (RBAC management)
            $g->get('/roles', [\KBuilder\Http\Controllers\Api\UserController::class, 'roles']);
            $g->get('/users', [\KBuilder\Http\Controllers\Api\UserController::class, 'index'])->add($perm('users.view'));
            $g->post('/users', [\KBuilder\Http\Controllers\Api\UserController::class, 'store'])->add($perm('users.create'));
            $g->get('/users/{id}', [\KBuilder\Http\Controllers\Api\UserController::class, 'show'])->add($perm('users.view'));
            $g->put('/users/{id}', [\KBuilder\Http\Controllers\Api\UserController::class, 'update'])->add($perm('users.edit'));
            $g->delete('/users/{id}', [\KBuilder\Http\Controllers\Api\UserController::class, 'destroy'])->add($perm('users.delete'));

        })->add($jwt);

        // ── Admin SPA ───────────────────────────────────────────────────
        // React handles routing client-side — serve index.html
        $app->get('/admin[/{path:.*}]', [PublicController::class, 'adminSpa']);

        // ── Public frontend catch-all ───────────────────────────────────
        // Phải nằm dưới cùng để không shadow các route tĩnh (như /api)
        $app->get('/{slug:[a-z0-9\-\/]+}', [PublicController::class, 'page']);
    }
}
