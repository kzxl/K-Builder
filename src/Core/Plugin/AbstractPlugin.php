<?php

declare(strict_types=1);

namespace KBuilder\Core\Plugin;

use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Admin\AdminMenuRegistry;
use Slim\App;

abstract class AbstractPlugin implements PluginInterface
{
    abstract public function getId(): string;
    
    abstract public function getName(): string;

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getType(): string
    {
        return 'extension';
    }

    public function boot(HookSystem $hooks): void
    {
    }

    public function registerComponents(ComponentRegistry $registry): void
    {
    }

    public function registerRoutes(App $app): void
    {
    }

    public function registerAdminMenus(AdminMenuRegistry $registry): void
    {
    }

    public function getMigrations(): array
    {
        return [];
    }
}
