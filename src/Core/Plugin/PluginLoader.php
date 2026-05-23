<?php

declare(strict_types=1);

namespace KBuilder\Core\Plugin;

use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Admin\AdminMenuRegistry;
use KBuilder\Core\Content\ContentTypeRegistry;
use Psr\Log\LoggerInterface;
use Slim\App;
use Illuminate\Database\Capsule\Manager as DB;

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
        private readonly ContentTypeRegistry $contentTypes,
        private readonly AdminMenuRegistry $adminMenus,
        private readonly LoggerInterface   $logger,
        private readonly \Twig\Environment $twig
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

            // Luôn đăng ký vào memory registry để Admin UI hiển thị đầy đủ danh sách
            $this->registry->register($plugin);

            $slug = $plugin->getId();
            
            // Đọc trạng thái hoạt động trong CSDL
            $dbPlugin = DB::table('plugins')->where('slug', $slug)->first();
            $isActive = true;

            if (!$dbPlugin) {
                // Plugin mới phát hiện lần đầu: Tự động chạy install()
                try {
                    $plugin->install();
                    $this->logger->info("Plugin installed successfully on auto-detect: {$slug}");
                } catch (\Throwable $instErr) {
                    $this->logger->error("Failed to install plugin on auto-detect: {$slug}", [
                        'error' => $instErr->getMessage(),
                    ]);
                }

                // Ghi nhận vào bảng plugins của DB với trạng thái active mặc định
                DB::table('plugins')->insert([
                    'slug'         => $slug,
                    'name'         => $plugin->getName(),
                    'version'      => $plugin->getVersion(),
                    'is_active'    => true,
                    'is_system'    => str_starts_with($slug, 'core-'),
                    'installed_at' => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
            } else {
                $isActive = (bool) $dbPlugin->is_active;
            }

            // Chỉ kích hoạt chạy logic nếu plugin ở trạng thái hoạt động (active)
            if ($isActive) {
                $plugin->boot($this->hooks);
                $plugin->registerComponents($this->components);
                $plugin->registerContentTypes($this->contentTypes);
                $plugin->registerRoutes($app);
                $plugin->registerAdminMenus($this->adminMenus);

                // Tự động đăng ký Twig namespace nếu có thư mục templates/
                $templatesDir = dirname($pluginFile) . '/templates';
                if (is_dir($templatesDir)) {
                    $loader = $this->twig->getLoader();
                    if ($loader instanceof \Twig\Loader\FilesystemLoader) {
                        $loader->addPath($templatesDir, $slug);
                    }
                }

                $this->logger->debug("Plugin loaded & activated: {$slug} v{$plugin->getVersion()}");
            } else {
                $this->logger->debug("Plugin detected but inactive (not loaded): {$slug}");
            }
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
