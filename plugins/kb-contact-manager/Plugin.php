<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbContactManager;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Hook\HookSystem;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string { return 'kb-contact-manager'; }
    public function getName(): string { return 'Contact Manager'; }
    public function getDescription(): string { return 'Thu thập và quản lý các lượt gửi Form liên hệ (Contact Submissions).'; }

    public function registerRoutes(App $app): void
    {
        // TODO: Đăng ký API nhận form liên hệ
    }
}
