<?php

declare(strict_types=1);

namespace KBuilder\Core\Plugin;

use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Admin\AdminMenuRegistry;
use Psr\Log\LoggerInterface;
use Slim\App;

/**
 * PluginLoader — Discover và khởi tạo tất cả plugin từ thư mục /plugins.
 *
 * Convention:
 *   plugins/
 *     {plugin-id}/
 *       Plugin.php          ← class {PluginNamespace}\Plugin implements PluginInterface
 *       plugin.json         ← metadata (optional, cho UI hiển thị)
 *       src/
 *       migrations/
 *       templates/
 */
class PluginLoader
{
    public function __construct(
        private readonly PluginRegistry    $registry,
        private readonly HookSystem        $hooks,
        private readonly ComponentRegistry $components,
        private readonly AdminMenuRegistry $adminMenus,
        private readonly LoggerInterface   $logger,
    ) {}

    public function loadAll(App $app): void
    {
        $pluginsPath = KB_ROOT . '/plugins';

        if (!is_dir($pluginsPath)) {
            return;
        }

        $dirs = glob($pluginsPath . '/*/Plugin.php');

        foreach ($dirs as $pluginFile) {
            $this->loadPlugin($pluginFile, $app);
        }

        $this->logger->info('Plugins loaded', ['count' => count($this->registry->all())]);
    }

    private function loadPlugin(string $pluginFile, App $app): void
    {
        try {
            require_once $pluginFile;

            // Derive class name từ directory name
            // plugins/core-hero/Plugin.php → KBuilder\Plugins\CoreHero\Plugin
            $dir = basename(dirname($pluginFile));
            $namespace = $this->dirToNamespace($dir);
            $className = "KBuilder\\Plugins\\{$namespace}\\Plugin";

            if (!class_exists($className)) {
                $this->logger->warning("Plugin class not found: {$className}");
                return;
            }

            /** @var PluginInterface $plugin */
            $plugin = new $className();

            if (!$plugin instanceof PluginInterface) {
                $this->logger->warning("{$className} does not implement PluginInterface");
                return;
            }

            // Lifecycle
            $plugin->boot($this->hooks);
            $plugin->registerComponents($this->components);
            $plugin->registerRoutes($app);
            $plugin->registerAdminMenus($this->adminMenus);

            $this->registry->register($plugin);

            $this->logger->debug("Plugin loaded: {$plugin->getId()} v{$plugin->getVersion()}");
        } catch (\Throwable $e) {
            $this->logger->error("Failed to load plugin: {$pluginFile}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Convert thư mục kebab-case → PascalCase namespace.
     * "core-hero" → "CoreHero"
     */
    private function dirToNamespace(string $dir): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $dir)));
    }
}
