<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreHero\Components;

use KBuilder\Core\Component\AbstractComponent;

class HeroFullwidthComponent extends AbstractComponent
{
    public function getType(): string  { return 'hero'; }
    public function getLabel(): string { return '🦸 Hero Fullwidth'; }
    public function getIcon(): string  { return '🦸'; }
    public function getGroup(): string { return 'hero'; }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties'  => [
                'title'          => ['type' => 'string', 'title' => 'Tiêu đề chính'],
                'subtitle'       => ['type' => 'string', 'format' => 'html', 'title' => 'Phụ đề'],
                'bg_image'       => ['type' => 'string', 'format' => 'image', 'title' => 'Ảnh nền'],
                'bg_color'       => ['type' => 'string', 'title' => 'Màu nền', 'default' => '#1e293b'],
                'bg_overlay'     => ['type' => 'string', 'title' => 'Độ mờ overlay (0-100)', 'default' => '50'],
                'text_align'     => ['type' => 'string', 'title' => 'Căn chữ', 'enum' => ['left', 'center', 'right'], 'default' => 'center'],
                'cta_text'       => ['type' => 'string', 'title' => 'Nút CTA', 'default' => 'Liên hệ ngay'],
                'cta_url'        => ['type' => 'string', 'title' => 'Link CTA'],
                'cta_style'      => ['type' => 'string', 'title' => 'Kiểu nút', 'enum' => ['primary', 'outline', 'ghost'], 'default' => 'primary'],
                'cta2_text'      => ['type' => 'string', 'title' => 'Nút CTA 2 (tùy chọn)'],
                'cta2_url'       => ['type' => 'string', 'title' => 'Link CTA 2'],
                'min_height'     => ['type' => 'string', 'title' => 'Chiều cao tối thiểu', 'enum' => ['400px', '500px', '600px', '100vh'], 'default' => '500px'],
                'scroll_arrow'   => ['type' => 'boolean', 'title' => 'Hiện mũi tên scroll', 'default' => false],
            ],
            'required' => ['title']
        ];
    }

    public function getDefaults(): array
    {
        return [
            'title'      => 'Chào mừng đến với website',
            'subtitle'   => 'Mô tả ngắn về doanh nghiệp của bạn',
            'bg_color'   => '#1e293b',
            'bg_overlay' => '50',
            'text_align' => 'center',
            'cta_text'   => 'Liên hệ ngay',
            'cta_url'    => '/contact',
            'cta_style'  => 'primary',
            'min_height' => '500px',
            'scroll_arrow' => false,
        ];
    }

    public function getTemplate(): string
    {
        return '@core-hero/fullwidth.twig';
    }
}
