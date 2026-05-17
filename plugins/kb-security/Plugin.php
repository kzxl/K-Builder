<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbSecurity;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Hook\HookSystem;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string { return 'kb-security'; }
    public function getName(): string { return 'Security & Rate Limiting'; }
    public function getDescription(): string { return 'Bảo vệ hệ thống bằng cách ghi log hành vi và giới hạn số lượng request API.'; }

    public function boot(HookSystem $hooks): void
    {
        // TODO: Đăng ký middleware bảo mật
    }
}
