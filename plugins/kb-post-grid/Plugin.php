<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbPostGrid;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Hook\HookSystem;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'kb-post-grid';
    }

    public function getName(): string
    {
        return 'Post Grid Component';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Hiển thị danh sách bài viết/sản phẩm động (Query Loop) hỗ trợ Taxonomy và Grid layout.';
    }

    public function boot(HookSystem $hooks): void
    {
    }

    public function registerComponents(ComponentRegistry $registry): void
    {
        require_once __DIR__ . '/Components/PostGridComponent.php';
        $registry->register(new \KBuilder\Plugins\KbPostGrid\Components\PostGridComponent());
    }

    public function registerRoutes(App $app): void
    {
    }
}
