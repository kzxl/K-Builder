<?php

declare(strict_types=1);

namespace KBuilder\Core\Component;

use RuntimeException;

/**
 * ComponentRegistry — Lưu trữ tất cả component types đã đăng ký.
 * Plugins gọi $registry->register(new HeroComponent()) trong registerComponents().
 */
class ComponentRegistry
{
    /** @var array<string, ComponentInterface> type => component */
    private array $components = [];

    public function register(ComponentInterface $component): void
    {
        $this->components[$component->getType()] = $component;
    }

    public function get(string $type): ComponentInterface
    {
        if (!isset($this->components[$type])) {
            throw new RuntimeException("Component type '{$type}' is not registered.");
        }
        return $this->components[$type];
    }

    public function has(string $type): bool
    {
        return isset($this->components[$type]);
    }

    /** @return ComponentInterface[] */
    public function all(): array
    {
        return array_values($this->components);
    }

    /**
     * Trả về danh sách component cho builder UI (nhóm theo group).
     */
    public function toBuilderList(): array
    {
        $list = [];
        foreach ($this->components as $component) {
            $list[] = [
                'type'     => $component->getType(),
                'name'     => $component->getLabel(),
                'category' => $component->getGroup(),
                'icon'     => $component->getIcon(),
                'schema'   => $component->getSchema(),
                'defaults' => $component->getDefaults(),
            ];
        }
        return $list;
    }

    /**
     * Trả về schema của một component type (cho API).
     */
    public function getSchema(string $type): array
    {
        return $this->get($type)->getSchema();
    }
}
