<?php

declare(strict_types=1);

use KBuilder\Core\Plugin\PluginLoader;
use KBuilder\Core\Plugin\PluginRegistry;
use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Content\ContentTypeRegistry;
use KBuilder\Core\Twig\ComponentExtension;
use KBuilder\Core\Router;
use KBuilder\Core\Cache\CacheManager;
use KBuilder\Domain\User\UserRepository;
use KBuilder\Domain\User\AuthService;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\get;
use function DI\factory;

return [
    // Logger
    LoggerInterface::class => factory(function () {
        $logger = new Logger('kbuilder');
        $logger->pushHandler(new StreamHandler(
            KB_ROOT . '/storage/logs/app.log',
            Logger::DEBUG
        ));
        return $logger;
    }),

    // Hook System — singleton
    HookSystem::class => autowire(HookSystem::class),

    // Plugin Registry + Loader
    PluginRegistry::class => autowire(PluginRegistry::class),
    PluginLoader::class   => autowire(PluginLoader::class),

    // Component Registry
    ComponentRegistry::class => autowire(ComponentRegistry::class),

    // Content Type Registry
    ContentTypeRegistry::class => factory(function () {
        $registry = new ContentTypeRegistry();
        
        // Đăng ký CPT và Taxonomy mặc định
        $registry->registerPostType('post', [
            'label' => 'Bài viết',
            'icon' => 'FileText',
            'taxonomies' => ['category']
        ]);
        
        $registry->registerTaxonomy('category', [
            'label' => 'Danh mục',
            'hierarchical' => true
        ]);
        
        return $registry;
    }),

    // Cache
    CacheManager::class => factory(function ($c) {
        $config = $c->get('config')['cache'];
        return new CacheManager($config);
    }),

    // Repositories
    UserRepository::class  => autowire(UserRepository::class),

    // Services
    AuthService::class => autowire(AuthService::class),



    // Router
    Router::class => autowire(Router::class),

    // Twig
    \Twig\Environment::class => factory(function ($c) {
        $config = $c->get('config')['app'];
        $loader = new \Twig\Loader\FilesystemLoader(KB_ROOT . '/templates');
        $twig = new \Twig\Environment($loader, [
            'cache'       => $config['debug'] ? false : KB_ROOT . '/storage/cache/twig',
            'auto_reload' => $config['debug'],
            'debug'       => $config['debug'],
        ]);
        if ($config['debug']) {
            $twig->addExtension(new \Twig\Extension\DebugExtension());
        }

        // Global URLs
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
        $twig->addGlobal('base_url', $baseUrl);
        $twig->addGlobal('asset_url', $assetUrl);

        // Đăng ký extension đệ quy
        $ext = new ComponentExtension($c->get(ComponentRegistry::class), $c->get(HookSystem::class));
        $ext->setTwig($twig);
        $twig->addExtension($ext);

        return $twig;
    }),
];
