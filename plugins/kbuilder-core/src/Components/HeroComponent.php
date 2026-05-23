<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbuilderCore\Components;

use KBuilder\Core\Component\AbstractComponent;

class HeroComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'core_hero';
    }

    public function getLabel(): string
    {
        return 'Hero Section';
    }

    public function getIcon(): string
    {
        return 'Image';
    }

    public function getGroup(): string
    {
        return 'Khối chính';
    }

    public function getSchema(): array
    {
        return [
            'version' => '1.0',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'title' => 'Tiêu đề chính',
                    'default' => 'Chào mừng đến với hệ thống'
                ],
                'subtitle' => [
                    'type' => 'string',
                    'title' => 'Mô tả phụ',
                    'default' => 'Đây là đoạn mô tả giới thiệu nội dung.'
                ],
                'bg_image' => [
                    'type' => 'string',
                    'title' => 'Ảnh nền',
                    'format' => 'image',
                    'default' => 'https://images.unsplash.com/photo-1557683316-973673baf926?auto=format&fit=crop&q=80&w=2000'
                ],
                'button_text' => [
                    'type' => 'string',
                    'title' => 'Chữ nút bấm',
                    'default' => 'Tìm hiểu thêm'
                ],
                'button_link' => [
                    'type' => 'string',
                    'title' => 'Đường dẫn nút bấm',
                    'default' => '#'
                ],
                'text_align' => [
                    'type' => 'string',
                    'title' => 'Căn lề',
                    'enum' => ['left', 'center', 'right'],
                    'default' => 'center'
                ]
            ]
        ];
    }

    public function getDefaults(): array
    {
        return [
            'title' => 'Chào mừng đến với hệ thống',
            'subtitle' => 'Đây là đoạn mô tả giới thiệu nội dung.',
            'bg_image' => 'https://images.unsplash.com/photo-1557683316-973673baf926?auto=format&fit=crop&q=80&w=2000',
            'button_text' => 'Tìm hiểu thêm',
            'button_link' => '#',
            'text_align' => 'center',
        ];
    }

    public function getTemplate(): string
    {
        return '@kbuilder-core/hero.twig';
    }
}
