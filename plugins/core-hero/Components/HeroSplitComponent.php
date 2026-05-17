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
            'version' => '1.0',
            'fields'  => [
                'title'       => ['type' => 'text',     'label' => 'Tiêu đề', 'required' => true],
                'subtitle'    => ['type' => 'textarea', 'label' => 'Mô tả'],
                'image'       => ['type' => 'media',    'label' => 'Ảnh', 'accept' => 'image/*'],
                'image_side'  => ['type' => 'select',   'label' => 'Vị trí ảnh', 'options' => ['left', 'right'], 'default' => 'right'],
                'cta_text'    => ['type' => 'text',     'label' => 'Nút CTA'],
                'cta_url'     => ['type' => 'url',      'label' => 'Link CTA'],
                'badges'      => ['type' => 'repeater', 'label' => 'Badges', 'fields' => [
                    'text' => ['type' => 'text', 'label' => 'Text'],
                    'icon' => ['type' => 'text', 'label' => 'Icon/emoji'],
                ]],
            ],
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

    public function getTemplate(): string { return 'components/hero/split.twig'; }
}
