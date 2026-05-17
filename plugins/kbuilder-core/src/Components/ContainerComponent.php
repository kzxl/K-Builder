<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbuilderCore\Components;

use KBuilder\Core\Component\AbstractComponent;

class ContainerComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'core_container';
    }

    public function getLabel(): string
    {
        return 'Layout Box (Lồng nhau)';
    }

    public function getIcon(): string
    {
        return 'Layout';
    }

    public function getGroup(): string
    {
        return 'Layout';
    }

    public function getSchema(): array
    {
        return [
            'version' => '1.0',
            'properties' => [
                'padding' => [
                    'type' => 'string',
                    'title' => 'Padding (CSS)',
                    'default' => '2rem 1rem'
                ],
                'background' => [
                    'type' => 'string',
                    'title' => 'Màu nền',
                    'default' => '#ffffff'
                ],
                'max_width' => [
                    'type' => 'string',
                    'title' => 'Độ rộng tối đa',
                    'default' => '1200px'
                ],
                'children' => [
                    'type' => 'component_list',
                    'title' => 'Các khối bên trong',
                    'default' => []
                ]
            ]
        ];
    }

    public function getDefaults(): array
    {
        return [
            'padding' => '2rem 1rem',
            'background' => '#ffffff',
            'max_width' => '1200px',
            'children' => []
        ];
    }

    public function getTemplate(): string
    {
        return '@kbuilder-core/container.twig';
    }
}
