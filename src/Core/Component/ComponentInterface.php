<?php

declare(strict_types=1);

namespace KBuilder\Core\Component;

/**
 * ComponentInterface — Contract cho mỗi section type.
 */
interface ComponentInterface
{
    /** Unique type key. VD: "hero", "hero_split", "blog_list" */
    public function getType(): string;

    /** Label hiển thị trong builder UI */
    public function getLabel(): string;

    /** Icon (emoji hoặc class) */
    public function getIcon(): string;

    /** Nhóm trong builder sidebar. VD: "layout", "content", "media" */
    public function getGroup(): string;

    /**
     * JSON Schema của props component.
     * Dùng để generate form editor trong React builder.
     *
     * @return array{version: string, fields: array<string, array{type: string, label: string, ...}>}
     */
    public function getSchema(): array;

    /**
     * Giá trị mặc định khi thêm mới component.
     */
    public function getDefaults(): array;

    /**
     * Twig template name để render. VD: "components/hero.twig"
     * Twig file tìm trong: templates/ (core) hoặc plugins/{id}/templates/
     */
    public function getTemplate(): string;

    /**
     * Migrate props từ schema version cũ → mới.
     * Trả về props đã được migrate, null nếu không cần.
     *
     * @param array  $props       Props hiện tại
     * @param string $fromVersion Schema version của props
     */
    public function migrateProps(array $props, string $fromVersion): ?array;
}
