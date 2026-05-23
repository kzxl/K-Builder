<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreFeatures\Components;

use KBuilder\Core\Component\AbstractComponent;

class FeaturesComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'core_features';
    }

    public function getLabel(): string
    {
        return 'Tính năng (Features)';
    }

    public function getIcon(): string
    {
        return 'grid';
    }

    public function getGroup(): string
    {
        return 'Sections';
    }

    public function getTemplate(): string
    {
        return '@core-features/default.twig';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'title' => 'Tiêu đề chính',
                    'default' => 'Tính năng nổi bật'
                ],
                'subtitle' => [
                    'type' => 'string',
                    'format' => 'html',
                    'title' => 'Mô tả ngắn',
                    'default' => 'Khám phá những điểm mạnh của chúng tôi.'
                ],
                'columns' => [
                    'type' => 'string',
                    'title' => 'Số cột',
                    'enum' => ['2', '3', '4'],
                    'default' => '3'
                ],
                'items' => [
                    'type' => 'array',
                    'title' => 'Danh sách tính năng',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'icon' => [
                                'type' => 'string',
                                'title' => 'Icon (Lucide name)',
                                'default' => 'check-circle'
                            ],
                            'title' => [
                                'type' => 'string',
                                'title' => 'Tên tính năng',
                                'default' => 'Tính năng 1'
                            ],
                            'description' => [
                                'type' => 'string',
                                'title' => 'Mô tả',
                                'default' => 'Mô tả chi tiết về tính năng này.'
                            ]
                        ]
                    ],
                    'default' => [
                        ['icon' => 'zap', 'title' => 'Tốc độ nhanh', 'description' => 'Tối ưu hóa hiệu suất tối đa.'],
                        ['icon' => 'shield', 'title' => 'Bảo mật cao', 'description' => 'Bảo vệ dữ liệu an toàn tuyệt đối.'],
                        ['icon' => 'smartphone', 'title' => 'Đa thiết bị', 'description' => 'Hiển thị tốt trên mọi kích thước màn hình.']
                    ]
                ]
            ]
        ];
    }
}
