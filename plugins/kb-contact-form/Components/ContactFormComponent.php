<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbContactForm\Components;

use KBuilder\Core\Component\AbstractComponent;

class ContactFormComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'kb-contact-form';
    }

    public function getLabel(): string
    {
        return 'Biểu mẫu liên hệ';
    }

    public function getIcon(): string
    {
        return 'Mail';
    }

    public function getCategory(): string
    {
        return 'Tương tác';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'heading' => [
                    'type' => 'string',
                    'title' => 'Tiêu đề Form',
                    'default' => 'Liên hệ với chúng tôi'
                ],
                'description' => [
                    'type' => 'string',
                    'title' => 'Mô tả ngắn',
                    'default' => 'Hãy để lại lời nhắn, chúng tôi sẽ phản hồi sớm nhất.'
                ],
                'buttonText' => [
                    'type' => 'string',
                    'title' => 'Chữ trên nút Gửi',
                    'default' => 'Gửi tin nhắn'
                ]
            ]
        ];
    }

    public function render(array $data): string
    {
        // Sử dụng template twig nội bộ của plugin
        $templatePath = dirname(__DIR__) . '/templates/form.twig';
        if (!file_exists($templatePath)) {
            return '<div style="color:red">Lỗi: Không tìm thấy template Contact Form</div>';
        }
        
        $twig = $this->getTwig();
        // Load trực tiếp file twig bằng đường dẫn tuyệt đối hoặc truyền template string
        $templateContent = file_get_contents($templatePath);
        $template = $twig->createTemplate($templateContent);
        
        // Cung cấp thêm action URL cho form
        $data['actionUrl'] = '/api/contact-form/submit';
        
        return $template->render($data);
    }
}
