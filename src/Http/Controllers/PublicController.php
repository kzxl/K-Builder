<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers;

use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Cache\CacheManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Twig\Environment;

class PublicController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ComponentRegistry $registry,
        private readonly HookSystem $hooks,
        private readonly CacheManager $cache
    ) {}

    public function home(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Lấy slug trang chủ từ site_settings
        $setting = DB::table('site_settings')
            ->where('key', 'homepage_slug')
            ->first();
            
        $slug = $setting ? $setting->value : 'trang-chu';
        
        return $this->renderPage($slug, $response, $request);
    }

    public function page(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->renderPage($args['slug'], $response, $request);
    }

    private function renderPage(string $slug, ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $isPreview = !empty($params['preview']) && $params['preview'] === '1';

        // Cache HTML cho trang published (bỏ qua preview để luôn xem bản nháp mới nhất)
        $cacheEnabled = !$isPreview;
        $cacheKey = 'page_html:' . $slug;

        if ($cacheEnabled) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                $response->getBody()->write($cached);
                return $response->withHeader('X-KB-Cache', 'HIT');
            }
        }

        $query = DB::table('pages')
            ->where('slug', $slug)
            ->whereNull('deleted_at');

        if (!$isPreview) {
            $query->where('status', 'published');
        }

        $page = $query->first();

        if (!$page) {
            $html = $this->twig->render('errors/404.twig');
            $response->getBody()->write($html);
            return $response->withStatus(404);
        }

        $layout = $page->layout ? json_decode($page->layout, true) : [];
        $seo = $page->seo ? json_decode($page->seo, true) : [];

        $themeSettings = DB::table('site_settings')
            ->where('group', 'theme')
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        // Lấy Header Menu
        $headerMenu = DB::table('menus')->where('location', 'header')->first();
        $headerMenuItems = [];
        if ($headerMenu) {
            $headerMenuItems = DB::table('menu_items')
                ->where('menu_id', $headerMenu->id)
                ->orderBy('sort_order', 'asc')
                ->get()
                ->toArray();
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);
        $basePath = str_replace('\\', '/', $basePath);
        $assetUrl = rtrim($basePath, '/');
        if ($assetUrl === '/') $assetUrl = '';

        $baseUrl = $assetUrl;
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        if ($requestUri && !str_starts_with($requestUri, $baseUrl)) {
            if (str_ends_with($baseUrl, '/public')) {
                $baseUrl = substr($baseUrl, 0, -7);
            }
        }

        $html = $this->twig->render('layouts/page.twig', [
            'page' => $page,
            'layout' => $layout,
            'seo' => $seo,
            'theme_vars' => $themeSettings,
            'header_menu' => $headerMenuItems,
            'base_url' => $baseUrl,
            'asset_url' => $assetUrl
        ]);

        $html = $this->hooks->applyFilters('kb_after_render_page', $html, $page);

        if ($cacheEnabled) {
            $this->cache->set($cacheKey, $html, 3600);
        }

        $response->getBody()->write($html);
        return $response->withHeader('X-KB-Cache', $cacheEnabled ? 'MISS' : 'BYPASS');
    }

    public function sitemap(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // TODO: Generate sitemap XML
        $response->getBody()->write('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');
        return $response->withHeader('Content-Type', 'text/xml');
    }

    /** Route để serve file tĩnh React Admin */
    public function adminSpa(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $indexPath = KB_ROOT . '/apps/admin/dist/index.html';
        
        if (!file_exists($indexPath)) {
            $response->getBody()->write('React Admin chưa được build. Vui lòng chạy "npm run build" trong thư mục apps/admin.');
            return $response->withHeader('Content-Type', 'text/html')->withStatus(503);
        }

        $html = file_get_contents($indexPath);
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }
}
