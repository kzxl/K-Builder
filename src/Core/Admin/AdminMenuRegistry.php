<?php

declare(strict_types=1);

namespace KBuilder\Core\Admin;

/**
 * AdminMenuRegistry — Plugin đăng ký menu vào đây.
 * Admin React SPA fetch GET /api/admin/menus để build sidebar.
 */
class AdminMenuRegistry
{
    private array $menus = [];

    public function add(
        string $id,
        string $label,
        string $icon,
        string $route,
        string $pluginId,
        int    $order = 50,
        string $permission = 'admin'
    ): void {
        $this->menus[] = compact('id', 'label', 'icon', 'route', 'pluginId', 'order', 'permission');
    }

    public function toArray(): array
    {
        usort($this->menus, fn($a, $b) => $a['order'] <=> $b['order']);
        return $this->menus;
    }
}
