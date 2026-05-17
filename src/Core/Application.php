<?php

declare(strict_types=1);

namespace KBuilder\Core;

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\App;
use Illuminate\Database\Capsule\Manager as Capsule;
use KBuilder\Core\Plugin\PluginLoader;
use KBuilder\Http\Middleware\ErrorMiddleware;
use KBuilder\Http\Middleware\CorsMiddleware;
use KBuilder\Http\Middleware\JsonMiddleware;

class Application
{
    private static ?App $app = null;

    public static function create(): App
    {
        $config = self::loadConfigs();

        // Boot Eloquent ORM
        self::bootDatabase($config['database']);

        // Build DI Container
        $container = self::buildContainer($config);

        // Create Slim App
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        self::$app = $app;

        // Add middleware
        $app->addRoutingMiddleware();
        $app->add(new JsonMiddleware());
        $app->add(new CorsMiddleware());

        $addErrorMiddleware = $app->addErrorMiddleware(
            $config['app']['debug'],
            true,
            true
        );

        // Set base path for subdirectory install
        $basePath = self::detectBasePath();
        if ($basePath) {
            $app->setBasePath($basePath);
        }

        // Load plugins (they register routes + components)
        $pluginLoader = $container->get(PluginLoader::class);
        $pluginLoader->loadAll($app);

        // Register core routes
        $router = $container->get(Router::class);
        $router->register($app);

        return $app;
    }

    private static function loadConfigs(): array
    {
        return [
            'app'      => require KB_ROOT . '/config/app.php',
            'database' => require KB_ROOT . '/config/database.php',
            'cache'    => require KB_ROOT . '/config/cache.php',
            'auth'     => require KB_ROOT . '/config/auth.php',
        ];
    }

    private static function bootDatabase(array $dbConfig): void
    {
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver'    => $dbConfig['driver'],
            'host'      => $dbConfig['host'],
            'port'      => $dbConfig['port'],
            'database'  => $dbConfig['database'],
            'username'  => $dbConfig['username'],
            'password'  => $dbConfig['password'],
            'charset'   => $dbConfig['charset'],
            'collation' => $dbConfig['collation'],
            'prefix'    => $dbConfig['prefix'],
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    private static function buildContainer(array $config): \DI\Container
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions(require KB_ROOT . '/src/Core/container.php');
        $builder->addDefinitions(['config' => $config]);

        if ($config['app']['env'] === 'production') {
            $builder->enableCompilation(KB_ROOT . '/storage/cache/di');
        }

        return $builder->build();
    }

    /**
     * Auto-detect base path khi chạy trong subdirectory (VD: /kbuilder/public)
     */
    private static function detectBasePath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName); // e.g. /kbuilder/public
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

        // Chuẩn hóa slashes
        $basePath = str_replace('\\', '/', $basePath);

        // Nếu request URI KHÔNG chứa basePath (tức là /public đã bị ẩn bởi htaccess ở thư mục gốc)
        if ($requestUri && !str_starts_with($requestUri, $basePath)) {
            if (str_ends_with($basePath, '/public')) {
                $basePath = substr($basePath, 0, -7);
            }
        }
        
        $basePath = rtrim($basePath, '/');

        return $basePath === '/' ? '' : $basePath;
    }
}
