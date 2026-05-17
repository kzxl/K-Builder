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
                    'format' => 'uri',
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
                ]
            ],
            'required' => ['image_url']
        ];
    }
}
