<?php

declare(strict_types=1);

namespace KBuilder\Core\Hook;

/**
 * HookSystem — Action/Filter engine thuần PHP.
 * API tương tự WordPress hooks nhưng không phụ thuộc WP.
 *
 * Usage:
 *   // Plugin đăng ký
 *   $hooks->addFilter('kbuilder/render_section', fn($html, $type) => $html, 10);
 *   $hooks->addAction('kbuilder/page_published', fn($page) => clearCache($page), 10);
 *
 *   // Core gọi
 *   $html = $hooks->applyFilters('kbuilder/render_section', $html, $type);
 *   $hooks->doAction('kbuilder/page_published', $page);
 */
class HookSystem
{
    /** @var array<string, array<int, array<array{callback: callable, priority: int}>>> */
    private array $filters = [];

    /** @var array<string, array<int, array<array{callback: callable, priority: int}>>> */
    private array $actions = [];

    // ─────────────────────────────────────────────
    // Filters — transform & return a value
    // ─────────────────────────────────────────────

    public function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        $this->filters[$hook][$priority][] = ['callback' => $callback];
    }

    public function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (!isset($this->filters[$hook])) {
            return $value;
        }

        ksort($this->filters[$hook]);

        foreach ($this->filters[$hook] as $callbacks) {
            foreach ($callbacks as $item) {
                $value = call_user_func($item['callback'], $value, ...$args);
            }
        }

        return $value;
    }

    public function removeFilter(string $hook, callable $callback, int $priority = 10): void
    {
        if (!isset($this->filters[$hook][$priority])) {
            return;
        }
        $this->filters[$hook][$priority] = array_filter(
            $this->filters[$hook][$priority],
            fn($item) => $item['callback'] !== $callback
        );
    }

    // ─────────────────────────────────────────────
    // Actions — side effects, no return value
    // ─────────────────────────────────────────────

    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        $this->actions[$hook][$priority][] = ['callback' => $callback];
    }

    public function doAction(string $hook, mixed ...$args): void
    {
        if (!isset($this->actions[$hook])) {
            return;
        }

        ksort($this->actions[$hook]);

        foreach ($this->actions[$hook] as $callbacks) {
            foreach ($callbacks as $item) {
                call_user_func($item['callback'], ...$args);
            }
        }
    }

    public function removeAction(string $hook, callable $callback, int $priority = 10): void
    {
        if (!isset($this->actions[$hook][$priority])) {
            return;
        }
        $this->actions[$hook][$priority] = array_filter(
            $this->actions[$hook][$priority],
            fn($item) => $item['callback'] !== $callback
        );
    }

    public function hasFilter(string $hook): bool
    {
        return !empty($this->filters[$hook]);
    }

    public function hasAction(string $hook): bool
    {
        return !empty($this->actions[$hook]);
    }
}
