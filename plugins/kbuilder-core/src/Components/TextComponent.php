<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbuilderCore\Components;

use KBuilder\Core\Component\AbstractComponent;

class TextComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'core_text';
    }

    public function getLabel(): string
    {
        return 'Văn bản (HTML)';
    }

    public function getIcon(): string
    {
        return 'Type';
    }

    public function getGroup(): string
    {
        return 'Nội dung';
    }

    public function getSchema(): array
    {
        return [
            'version' => '1.0',
            'properties' => [
                'content' => [
                    'type' => 'string',
                    'format' => 'html',
                    'title' => 'Nội dung HTML',
                    'default' => '<p>Đây là khối văn bản mẫu.</p>'
                ]
            ]
        ];
    }

    public function getDefaults(): array
    {
        return [
            'content' => '<p>Đây là khối văn bản mẫu.</p>'
        ];
    }

    public function getTemplate(): string
    {
        return '@kbuilder-core/text.twig';
    }
}
