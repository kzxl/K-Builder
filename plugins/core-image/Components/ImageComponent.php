<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreImage\Components;

use KBuilder\Core\Component\AbstractComponent;

class ImageComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'core_image';
    }

    public function getLabel(): string
    {
        return 'Hình ảnh';
    }

    public function getGroup(): string
    {
        return 'Media';
    }

    public function getIcon(): string
    {
        return 'image';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'image_url' => [
                    'type' => 'string',
                    'format' => 'image',
                    'title' => 'Đường dẫn ảnh'
                ],
                'alt_text' => [
                    'type' => 'string',
                    'title' => 'Văn bản thay thế (Alt)'
                ],
                'caption' => [
                    'type' => 'string',
                    'title' => 'Chú thích'
                ],
                'border_radius' => [
                    'type' => 'string',
                    'title' => 'Bo góc',
                    'default' => '8px'
                ],
                'box_shadow' => [
                    'type' => 'boolean',
                    'title' => 'Bóng đổ',
                    'default' => false
                ],
                'full_width' => [
                    'type' => 'boolean',
                    'title' => 'Tràn viền (Full width)',
                    'default' => false
                ]
            ],
            'required' => []
        ];
    }

    public function getDefaults(): array
    {
        return [
            'image_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop',
            'alt_text' => 'Ảnh mô tả',
            'caption' => '',
            'border_radius' => '8px',
            'box_shadow' => false,
            'full_width' => false
        ];
    }

    public function getTemplate(): string
    {
        return '@core-image/image.twig';
    }
}
