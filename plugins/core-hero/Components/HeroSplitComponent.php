<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreHero\Components;

use KBuilder\Core\Component\AbstractComponent;

class HeroSplitComponent extends AbstractComponent
{
    public function getType(): string  { return 'hero_split'; }
    public function getLabel(): string { return '↔️ Hero Split (Image + Text)'; }
    public function getIcon(): string  { return '↔️'; }
    public function getGroup(): string { return 'hero'; }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties'  => [
                'title'       => ['type' => 'string', 'title' => 'Tiêu đề'],
                'subtitle'    => ['type' => 'string', 'format' => 'html', 'title' => 'Mô tả'],
                'image'       => ['type' => 'string', 'format' => 'image', 'title' => 'Ảnh nền'],
                'image_side'  => ['type' => 'string', 'title' => 'Vị trí ảnh', 'enum' => ['left', 'right'], 'default' => 'right'],
                'cta_text'    => ['type' => 'string', 'title' => 'Nút CTA'],
                'cta_url'     => ['type' => 'string', 'title' => 'Link CTA'],
                'badges'      => [
                    'type' => 'array', 
                    'title' => 'Badges', 
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'text' => ['type' => 'string', 'title' => 'Text'],
                            'icon' => ['type' => 'string', 'title' => 'Icon/emoji'],
                        ]
                    ]
                ],
            ],
            'required' => ['title']
        ];
    }

    public function getDefaults(): array
    {
        return [
            'title'      => 'Giải pháp dành cho doanh nghiệp',
            'subtitle'   => 'Mô tả chi tiết về sản phẩm/dịch vụ của bạn.',
            'image_side' => 'right',
            'cta_text'   => 'Tìm hiểu thêm',
            'badges'     => [],
        ];
    }

    public function getTemplate(): string { return '@core-hero/split.twig'; }
}
