<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbSeoManager;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Hook\HookSystem;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string { return 'kb-seo-manager'; }
    public function getName(): string { return 'SEO Manager'; }
    public function getDescription(): string { return 'Quản lý thẻ Meta, OpenGraph và Sitemap tự động.'; }

    public function boot(HookSystem $hooks): void
    {
        // TODO: Móc vào lúc render meta tags
    }
}
