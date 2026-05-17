<?php

declare(strict_types=1);

namespace KBuilder\Core\Content;

class ContentTypeRegistry
{
    /**
     * @var array<string, array>
     */
    private array $postTypes = [];

    /**
     * @var array<string, array>
     */
    private array $taxonomies = [];

    /**
     * Đăng ký một Custom Post Type mới
     * 
     * @param string $type Mã định danh (vd: 'post', 'product')
     * @param array $config Cấu hình (nhãn, biểu tượng, hỗ trợ editor...)
     */
    public function registerPostType(string $type, array $config): void
    {
        $this->postTypes[$type] = array_merge([
            'label' => ucfirst($type),
            'icon' => 'FileText',
            'supports' => ['title', 'content', 'excerpt', 'featured_image', 'taxonomies'],
            'taxonomies' => [], // Các taxonomy hỗ trợ mặc định
        ], $config);
    }

    /**
     * Đăng ký một Taxonomy mới
     * 
     * @param string $type Mã định danh (vd: 'category', 'product_cat')
     * @param array $config Cấu hình (nhãn, phân cấp...)
     */
    public function registerTaxonomy(string $type, array $config): void
    {
        $this->taxonomies[$type] = array_merge([
            'label' => ucfirst($type),
            'hierarchical' => true,
        ], $config);
    }

    public function getPostTypes(): array
    {
        return $this->postTypes;
    }

    public function getPostType(string $type): ?array
    {
        return $this->postTypes[$type] ?? null;
    }

    public function hasPostType(string $type): bool
    {
        return isset($this->postTypes[$type]);
    }

    public function getTaxonomies(): array
    {
        return $this->taxonomies;
    }

    public function getTaxonomy(string $type): ?array
    {
        return $this->taxonomies[$type] ?? null;
    }
}
