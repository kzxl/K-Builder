<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreText\Components;

use KBuilder\Core\Component\AbstractComponent;

class TextComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'core_text';
    }

    public function getTemplate(): string
    {
        return '@core-text/text.twig';
    }

    public function getLabel(): string
    {
        return 'Text Block';
    }

    public function getGroup(): string
    {
        return 'Typography';
    }

    public function getIcon(): string
    {
        return 'align-left';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'content' => [
                    'type' => 'string',
                    'format' => 'html',
                    'title' => 'Nội dung',
                    'default' => '<p>Nhập nội dung văn bản của bạn vào đây.</p>'
                ],
                'alignment' => [
                    'type' => 'string',
                    'title' => 'Căn lề',
                    'enum' => ['left', 'center', 'right', 'justify'],
                    'default' => 'left'
                ],
                'max_width' => [
                    'type' => 'string',
                    'title' => 'Độ rộng tối đa',
                    'default' => '800px'
                ]
            ],
            'required' => ['content']
        ];
    }
}
