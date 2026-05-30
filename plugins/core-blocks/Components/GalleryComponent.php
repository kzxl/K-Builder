<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreBlocks\Components;

use KBuilder\Core\Component\AbstractComponent;

class GalleryComponent extends AbstractComponent
{
    public function getType(): string { return 'core_gallery'; }
    public function getLabel(): string { return 'Thư viện ảnh'; }
    public function getIcon(): string { return 'Images'; }
    public function getGroup(): string { return 'Media'; }
    public function getTemplate(): string { return '@core-blocks/gallery.twig'; }

    public function getDefaults(): array
    {
        return [
            'title'   => '',
            'columns' => '3',
            'gap'     => '12px',
            'rounded' => true,
            'items'   => [
                ['image' => '', 'alt' => 'Ảnh 1', 'caption' => ''],
                ['image' => '', 'alt' => 'Ảnh 2', 'caption' => ''],
                ['image' => '', 'alt' => 'Ảnh 3', 'caption' => ''],
            ],
        ];
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'version' => '1.0',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'title' => 'Tiêu đề (tùy chọn)',
                    'default' => '',
                ],
                'columns' => [
                    'type' => 'string',
                    'title' => 'Số cột',
                    'enum' => ['2', '3', '4', '5'],
                    'default' => '3',
                ],
                'gap' => [
                    'type' => 'string',
                    'title' => 'Khoảng cách',
                    'default' => '12px',
                ],
                'rounded' => [
                    'type' => 'boolean',
                    'title' => 'Bo góc ảnh',
                    'default' => true,
                ],
                'items' => [
                    'type' => 'array',
                    'title' => 'Danh sách ảnh',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'image'   => ['type' => 'string', 'format' => 'image', 'title' => 'Ảnh', 'default' => ''],
                            'alt'     => ['type' => 'string', 'title' => 'Mô tả ảnh (alt)', 'default' => ''],
                            'caption' => ['type' => 'string', 'title' => 'Chú thích', 'default' => ''],
                        ],
                    ],
                    'default' => [],
                ],
            ],
        ];
    }
}
