<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbPostGrid\Components;

use KBuilder\Core\Component\AbstractComponent;

class PostGridComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'kb-post-grid';
    }

    public function getLabel(): string
    {
        return 'Danh sách Bài viết / Sản phẩm';
    }

    public function getIcon(): string
    {
        return 'LayoutGrid';
    }

    public function getGroup(): string
    {
        return 'Dữ liệu động';
    }

    public function getTemplate(): string
    {
        return 'plugins/kb-post-grid/templates/grid.twig';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'supports_dynamic_data' => true,
            'title' => 'Danh sách dữ liệu',
            'properties' => [
                'heading' => [
                    'type' => 'string',
                    'title' => 'Tiêu đề khối',
                    'default' => 'Bài viết mới nhất'
                ],
                'layout_style' => [
                    'type' => 'string',
                    'title' => 'Kiểu hiển thị',
                    'enum' => ['grid-2', 'grid-3', 'grid-4'],
                    'default' => 'grid-3'
                ]
            ]
        ];
    }
}
