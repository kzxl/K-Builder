<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreButton\Components;

use KBuilder\Core\Component\AbstractComponent;

class ButtonComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'core_button';
    }

    public function getLabel(): string
    {
        return 'Nút bấm (CTA)';
    }

    public function getIcon(): string
    {
        return 'mouse-pointer-click';
    }

    public function getGroup(): string
    {
        return 'UI Elements';
    }

    public function getTemplate(): string
    {
        return 'components/button/default.twig';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'text' => [
                    'type' => 'string',
                    'title' => 'Nhãn nút',
                    'default' => 'Bấm vào đây'
                ],
                'url' => [
                    'type' => 'string',
                    'title' => 'Đường dẫn',
                    'default' => '#'
                ],
                'style' => [
                    'type' => 'string',
                    'title' => 'Kiểu nút',
                    'enum' => ['primary', 'secondary', 'outline', 'ghost'],
                    'default' => 'primary'
                ],
                'size' => [
                    'type' => 'string',
                    'title' => 'Kích thước',
                    'enum' => ['sm', 'md', 'lg'],
                    'default' => 'md'
                ],
                'alignment' => [
                    'type' => 'string',
                    'title' => 'Căn lề',
                    'enum' => ['left', 'center', 'right'],
                    'default' => 'center'
                ]
            ],
            'required' => ['text', 'url']
        ];
    }
}
