<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreFaq\Components;

use KBuilder\Core\Component\AbstractComponent;

class FaqComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'core_faq';
    }

    public function getLabel(): string
    {
        return 'Hỏi đáp (FAQ)';
    }

    public function getIcon(): string
    {
        return 'message-circle-question';
    }

    public function getGroup(): string
    {
        return 'Sections';
    }

    public function getTemplate(): string
    {
        return '@core-faq/default.twig';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'title' => 'Tiêu đề',
                    'default' => 'Câu hỏi thường gặp'
                ],
                'items' => [
                    'type' => 'array',
                    'title' => 'Danh sách câu hỏi',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'question' => [
                                'type' => 'string',
                                'title' => 'Câu hỏi',
                                'default' => 'Câu hỏi mẫu?'
                            ],
                            'answer' => [
                                'type' => 'string',
                                'format' => 'html',
                                'title' => 'Câu trả lời',
                                'default' => 'Trả lời chi tiết cho câu hỏi.'
                            ]
                        ]
                    ],
                    'default' => [
                        ['question' => 'Dịch vụ của bạn có miễn phí không?', 'answer' => 'Chúng tôi cung cấp gói miễn phí cơ bản và các gói trả phí với nhiều tính năng nâng cao.'],
                        ['question' => 'Tôi có thể hủy đăng ký bất cứ lúc nào không?', 'answer' => 'Có, bạn có thể hủy gói đăng ký của mình bất kỳ lúc nào mà không bị ràng buộc hợp đồng.']
                    ]
                ]
            ]
        ];
    }
}
