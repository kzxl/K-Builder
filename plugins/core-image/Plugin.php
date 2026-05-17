<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreImage;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Hook\HookSystem;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'core-image';
    }

    public function getName(): string
    {
        return 'Core Image Plugin';
    }

    public function getDescription(): string
    {
        return 'Component hiển thị Hình ảnh, hỗ trợ Lazy Load và chỉnh kích thước.';
    }

    public function getType(): string
    {
        return 'content';
    }

    public function boot(HookSystem $hooks): void
    {
    }

    public function registerComponents(ComponentRegistry $registry): void
    {
        $registry->register(new Components\ImageComponent());
    }

    public function registerRoutes(App $app): void
    {
    }
}
