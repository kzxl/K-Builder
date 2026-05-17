<?php

declare(strict_types=1);

namespace KBuilder\Core\Plugin;

use RuntimeException;

/**
 * PluginRegistry — Lưu trữ và tra cứu plugin đã đăng ký.
 */
class PluginRegistry
{
    /** @var array<string, PluginInterface> slug => plugin instance */
    private array $plugins = [];

    public function register(PluginInterface $plugin): void
    {
        $id = $plugin->getId();

        if (isset($this->plugins[$id])) {
            throw new RuntimeException("Plugin '{$id}' is already registered.");
        }

        $this->plugins[$id] = $plugin;
    }

    public function get(string $id): ?PluginInterface
    {
        return $this->plugins[$id] ?? null;
    }

    /** @return PluginInterface[] */
    public function all(): array
    {
        return array_values($this->plugins);
    }

    public function has(string $id): bool
    {
        return isset($this->plugins[$id]);
    }

    /** @return string[] */
    public function ids(): array
    {
        return array_keys($this->plugins);
    }

    /**
     * Trả về metadata tất cả plugins cho Admin API.
     */
    public function toApiList(): array
    {
        return array_map(fn(PluginInterface $p) => [
            'id'          => $p->getId(),
            'name'        => $p->getName(),
            'version'     => $p->getVersion(),
            'description' => $p->getDescription(),
        ], array_values($this->plugins));
    }
}
