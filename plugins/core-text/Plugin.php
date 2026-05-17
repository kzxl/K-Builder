<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreText;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Hook\HookSystem;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'core-text';
    }

    public function getName(): string
    {
        return 'Core Text Plugin';
    }

    public function boot(HookSystem $hooks): void
    {
        // Init logic nếu cần
    }

    public function registerComponents(ComponentRegistry $registry): void
    {
        $registry->register(new Components\TextComponent());
    }

    public function registerRoutes(App $app): void
    {
        // Plugin này không cần custom routes
    }
}
