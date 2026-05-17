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
            'version' => '1.0',
            'fields'  => [
                'title'          => ['type' => 'text',     'label' => 'Tiêu đề chính',    'required' => true],
                'subtitle'       => ['type' => 'textarea', 'label' => 'Phụ đề',           'required' => false],
                'bg_image'       => ['type' => 'media',    'label' => 'Ảnh nền',          'accept' => 'image/*'],
                'bg_color'       => ['type' => 'color',    'label' => 'Màu nền',          'default' => '#1e293b'],
                'bg_overlay'     => ['type' => 'range',    'label' => 'Độ mờ overlay',    'min' => 0, 'max' => 100, 'default' => 50],
                'text_align'     => ['type' => 'select',   'label' => 'Căn chữ',          'options' => ['left', 'center', 'right'], 'default' => 'center'],
                'cta_text'       => ['type' => 'text',     'label' => 'Nút CTA',          'default' => 'Liên hệ ngay'],
                'cta_url'        => ['type' => 'url',      'label' => 'Link CTA'],
                'cta_style'      => ['type' => 'select',   'label' => 'Kiểu nút',         'options' => ['primary', 'outline', 'ghost'], 'default' => 'primary'],
                'cta2_text'      => ['type' => 'text',     'label' => 'Nút CTA 2 (tùy chọn)'],
                'cta2_url'       => ['type' => 'url',      'label' => 'Link CTA 2'],
                'min_height'     => ['type' => 'select',   'label' => 'Chiều cao tối thiểu', 'options' => ['400px', '500px', '600px', '100vh'], 'default' => '500px'],
                'scroll_arrow'   => ['type' => 'toggle',   'label' => 'Hiện mũi tên scroll', 'default' => false],
            ],
        ];
    }

    public function getDefaults(): array
    {
        return [
            'title'      => 'Chào mừng đến với website',
            'subtitle'   => 'Mô tả ngắn về doanh nghiệp của bạn',
            'bg_color'   => '#1e293b',
            'bg_overlay' => 50,
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
        return 'components/hero/fullwidth.twig';
    }
}
