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
            'type' => 'object',
            'properties' => [
                'badge_text'   => ['type' => 'string', 'title' => 'Badge text (tùy chọn)'],
                'title'        => ['type' => 'string', 'title' => 'Tiêu đề'],
                'highlight'    => ['type' => 'string', 'title' => 'Từ nổi bật (highlight màu)'],
                'subtitle'     => ['type' => 'string', 'format' => 'html', 'title' => 'Phụ đề'],
                'cta_text'     => ['type' => 'string', 'title' => 'Nút chính'],
                'cta_url'      => ['type' => 'string', 'title' => 'Link nút chính'],
                'cta2_text'    => ['type' => 'string', 'title' => 'Nút phụ'],
                'cta2_url'     => ['type' => 'string', 'title' => 'Link nút phụ'],
                'bg_gradient'  => ['type' => 'boolean', 'title' => 'Nền gradient', 'default' => true],
            ],
            'required' => ['title']
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

    public function getTemplate(): string { return '@core-hero/minimal.twig'; }
}
