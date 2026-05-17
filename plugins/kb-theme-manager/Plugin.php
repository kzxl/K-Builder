<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbThemeManager;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Admin\AdminMenuRegistry;

class Plugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'kb-theme-manager';
    }

    public function getName(): string
    {
        return 'Theme Manager';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Quản lý giao diện, màu sắc, bo góc và tuỳ biến CSS cho Frontend.';
    }

    public function getType(): string
    {
        return 'system';
    }

    public function registerAdminMenus(AdminMenuRegistry $registry): void
    {
        $registry->add(
            id: 'theme_manager',
            label: 'Giao diện & CSS',
            icon: 'Briefcase',
            route: '/plugins/kb-theme-manager',
            pluginId: $this->getId(),
            order: 15
        );
    }
}
