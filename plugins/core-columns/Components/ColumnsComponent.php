<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreColumns\Components;

use KBuilder\Core\Component\AbstractComponent;

class ColumnsComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'core_columns';
    }

    public function getLabel(): string
    {
        return 'Chia cột (Columns)';
    }

    public function getIcon(): string
    {
        return 'columns';
    }

    public function getGroup(): string
    {
        return 'Layout';
    }

    public function getTemplate(): string
    {
        return '@core-columns/default.twig';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'layout' => [
                    'type' => 'string',
                    'title' => 'Bố cục cột',
                    'enum' => ['1-1', '1-2', '2-1', '1-1-1', '1-1-1-1'],
                    'default' => '1-1'
                ],
                'gap' => [
                    'type' => 'string',
                    'title' => 'Khoảng cách (Gap)',
                    'enum' => ['0', '1rem', '2rem', '3rem'],
                    'default' => '2rem'
                ],
                'col1_children' => [
                    'type' => 'component_list',
                    'title' => 'Nội dung cột 1',
                    'default' => []
                ],
                'col2_children' => [
                    'type' => 'component_list',
                    'title' => 'Nội dung cột 2',
                    'default' => []
                ],
                'col3_children' => [
                    'type' => 'component_list',
                    'title' => 'Nội dung cột 3',
                    'default' => []
                ],
                'col4_children' => [
                    'type' => 'component_list',
                    'title' => 'Nội dung cột 4',
                    'default' => []
                ]
            ]
        ];
    }
}
