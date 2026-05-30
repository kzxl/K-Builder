<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbFormBuilder\Components;

use KBuilder\Core\Component\AbstractComponent;

/**
 * Component Form Builder: cho phép tạo form tùy biến bằng danh sách field động.
 */
class FormBuilderComponent extends AbstractComponent
{
    public function getType(): string
    {
        return 'kb_form';
    }

    public function getLabel(): string
    {
        return 'Form tùy biến';
    }

    public function getIcon(): string
    {
        return 'ClipboardList';
    }

    public function getGroup(): string
    {
        return 'Tương tác';
    }

    public function getTemplate(): string
    {
        return '@kb-form-builder/form.twig';
    }

    public function getDefaults(): array
    {
        return [
            'heading'     => 'Đăng ký nhận tin',
            'description' => 'Điền thông tin bên dưới để liên hệ với chúng tôi.',
            'buttonText'  => 'Gửi',
            'form_key'    => 'default',
            'fields'      => [
                ['label' => 'Họ và tên', 'name' => 'name', 'type' => 'text', 'required' => true, 'placeholder' => ''],
                ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'required' => true, 'placeholder' => ''],
                ['label' => 'Lời nhắn', 'name' => 'message', 'type' => 'textarea', 'required' => false, 'placeholder' => ''],
            ],
        ];
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'version' => '1.0',
            'properties' => [
                'heading' => [
                    'type' => 'string',
                    'title' => 'Tiêu đề Form',
                    'default' => 'Đăng ký nhận tin',
                ],
                'description' => [
                    'type' => 'string',
                    'title' => 'Mô tả ngắn',
                    'default' => 'Điền thông tin bên dưới để liên hệ với chúng tôi.',
                ],
                'form_key' => [
                    'type' => 'string',
                    'title' => 'Mã form (phân loại submission)',
                    'default' => 'default',
                ],
                'buttonText' => [
                    'type' => 'string',
                    'title' => 'Chữ trên nút Gửi',
                    'default' => 'Gửi',
                ],
                'fields' => [
                    'type' => 'array',
                    'title' => 'Các trường nhập liệu',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'label' => [
                                'type' => 'string',
                                'title' => 'Nhãn',
                                'default' => 'Trường mới',
                            ],
                            'name' => [
                                'type' => 'string',
                                'title' => 'Khóa (name)',
                                'default' => 'field',
                            ],
                            'type' => [
                                'type' => 'string',
                                'title' => 'Kiểu',
                                'enum' => ['text', 'email', 'tel', 'number', 'textarea', 'select'],
                                'default' => 'text',
                            ],
                            'required' => [
                                'type' => 'boolean',
                                'title' => 'Bắt buộc',
                                'default' => false,
                            ],
                            'placeholder' => [
                                'type' => 'string',
                                'title' => 'Placeholder',
                                'default' => '',
                            ],
                            'options' => [
                                'type' => 'string',
                                'title' => 'Tùy chọn (cho select, ngăn cách dấu phẩy)',
                                'default' => '',
                            ],
                        ],
                    ],
                    'default' => [
                        ['label' => 'Họ và tên', 'name' => 'name', 'type' => 'text', 'required' => true, 'placeholder' => ''],
                        ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'required' => true, 'placeholder' => ''],
                        ['label' => 'Lời nhắn', 'name' => 'message', 'type' => 'textarea', 'required' => false, 'placeholder' => ''],
                    ],
                ],
            ],
        ];
    }
}
