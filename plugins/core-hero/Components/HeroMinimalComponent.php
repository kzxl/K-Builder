<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreHero\Components;

use KBuilder\Core\Component\AbstractComponent;

class HeroMinimalComponent extends AbstractComponent
{
    public function getType(): string  { return 'hero_minimal'; }
    public function getLabel(): string { return '✦ Hero Minimal'; }
    public function getIcon(): string  { return '✦'; }
    public function getGroup(): string { return 'hero'; }

    public function getSchema(): array
    {
        return [
            'version' => '1.0',
            'fields'  => [
                'badge_text'   => ['type' => 'text',   'label' => 'Badge text (tùy chọn)'],
                'title'        => ['type' => 'text',   'label' => 'Tiêu đề', 'required' => true],
                'highlight'    => ['type' => 'text',   'label' => 'Từ nổi bật (highlight màu)'],
                'subtitle'     => ['type' => 'textarea', 'label' => 'Phụ đề'],
                'cta_text'     => ['type' => 'text',   'label' => 'Nút chính'],
                'cta_url'      => ['type' => 'url',    'label' => 'Link nút chính'],
                'cta2_text'    => ['type' => 'text',   'label' => 'Nút phụ'],
                'cta2_url'     => ['type' => 'url',    'label' => 'Link nút phụ'],
                'bg_gradient'  => ['type' => 'toggle', 'label' => 'Nền gradient', 'default' => true],
            ],
        ];
    }

    public function getDefaults(): array
    {
        return [
            'badge_text'  => '🚀 New Release',
            'title'       => 'Build website',
            'highlight'   => 'siêu nhanh',
            'subtitle'    => 'Drag & drop builder mạnh mẽ, SEO tốt, custom sâu.',
            'cta_text'    => 'Bắt đầu miễn phí',
            'cta_url'     => '/register',
            'cta2_text'   => 'Xem demo',
            'cta2_url'    => '/demo',
            'bg_gradient' => true,
        ];
    }

    public function getTemplate(): string { return 'components/hero/minimal.twig'; }
}
