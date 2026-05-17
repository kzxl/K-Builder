<?php

declare(strict_types=1);

namespace KBuilder\Core\Component;

/**
 * AbstractComponent — Base class giúp plugin không phải implement hết interface.
 */
abstract class AbstractComponent implements ComponentInterface
{
    public function getLabel(): string
    {
        return 'Component';
    }

    public function getIcon(): string
    {
        return '📦';
    }

    public function getGroup(): string
    {
        return 'Khác';
    }

    public function getDefaults(): array
    {
        return [];
    }

    public function getTemplate(): string
    {
        return 'components/' . $this->getType() . '.twig';
    }

    public function migrateProps(array $props, string $fromVersion): ?array
    {
        // Default: không cần migrate
        return null;
    }

    /**
     * Merge defaults vào props đã lưu — tránh missing field khi schema mở rộng.
     */
    public function resolveProps(array $savedProps): array
    {
        return array_merge($this->getDefaults(), $savedProps);
    }

    /**
     * Lấy schema version hiện tại.
     */
    public function getSchemaVersion(): string
    {
        return $this->getSchema()['version'] ?? '1.0';
    }
}
