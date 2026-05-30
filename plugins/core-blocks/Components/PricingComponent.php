<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreBlocks\Components;

use KBuilder\Core\Component\AbstractComponent;

class PricingComponent extends AbstractComponent
{
    public function getType(): string { return 'core_pricing'; }
    public function getLabel(): string { return 'Bảng giá'; }
    public function getIcon(): string { return 'CreditCard'; }
    public function getGroup(): string { return 'Sections'; }
    public function getTemplate(): string { return '@core-blocks/pricing.twig'; }

    public function getDefaults(): array
    {
        return [
            'title' => 'Bảng giá dịch vụ',
            'subtitle' => 'Chọn gói phù hợp với nhu cầu của bạn.',
            'plans' => [
                [
                    'name' => 'Cơ bản', 'price' => '0đ', 'period' => '/tháng',
                    'features' => "1 website\n5 trang\nHỗ trợ email",
                    'cta_text' => 'Bắt đầu', 'cta_url' => '#', 'featured' => false,
                ],
                [
                    'name' => 'Chuyên nghiệp', 'price' => '299k', 'period' => '/tháng',
                    'features' => "5 website\nKhông giới hạn trang\nHỗ trợ ưu tiên\nGỡ branding",
                    'cta_text' => 'Đăng ký', 'cta_url' => '#', 'featured' => true,
                ],
                [
                    'name' => 'Doanh nghiệp', 'price' => 'Liên hệ', 'period' => '',
                    'features' => "Không giới hạn\nQuản lý chuyên biệt\nSLA 99.9%",
                    'cta_text' => 'Liên hệ', 'cta_url' => '#', 'featured' => false,
                ],
            ],
        ];
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'version' => '1.0',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'title' => 'Tiêu đề',
                    'default' => 'Bảng giá dịch vụ',
                ],
                'subtitle' => [
                    'type' => 'string',
                    'format' => 'html',
                    'title' => 'Mô tả ngắn',
                    'default' => 'Chọn gói phù hợp với nhu cầu của bạn.',
                ],
                'plans' => [
                    'type' => 'array',
                    'title' => 'Các gói giá',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name'     => ['type' => 'string', 'title' => 'Tên gói', 'default' => 'Gói mới'],
                            'price'    => ['type' => 'string', 'title' => 'Giá', 'default' => '0đ'],
                            'period'   => ['type' => 'string', 'title' => 'Chu kỳ', 'default' => '/tháng'],
                            'features' => ['type' => 'string', 'format' => 'html', 'title' => 'Tính năng (mỗi dòng một mục)', 'default' => "Tính năng 1\nTính năng 2"],
                            'cta_text' => ['type' => 'string', 'title' => 'Chữ nút', 'default' => 'Chọn gói'],
                            'cta_url'  => ['type' => 'string', 'title' => 'Link nút', 'default' => '#'],
                            'featured' => ['type' => 'boolean', 'title' => 'Gói nổi bật', 'default' => false],
                        ],
                    ],
                    'default' => [],
                ],
            ],
        ];
    }
}
