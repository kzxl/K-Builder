<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreFaq;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Hook\HookSystem;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'core-faq';
    }

    public function getName(): string
    {
        return 'Core FAQ Plugin';
    }

    public function boot(HookSystem $hooks): void
    {
    }

    public function registerComponents(ComponentRegistry $registry): void
    {
        $registry->register(new Components\FaqComponent());
    }

    public function registerRoutes(App $app): void
    {
    }
}
