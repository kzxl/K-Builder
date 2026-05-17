<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreHero;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Hook\HookSystem;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Admin\AdminMenuRegistry;
use Slim\App;

class Plugin extends AbstractPlugin
{
    public function getId(): string          { return 'core-hero'; }
    public function getName(): string        { return 'Core Hero Sections'; }
    public function getDescription(): string { return 'Các loại Hero section: Fullwidth, Split, Video, Minimal'; }

    public function registerComponents(ComponentRegistry $registry): void
    {
        $registry->register(new Components\HeroFullwidthComponent());
        $registry->register(new Components\HeroSplitComponent());
        $registry->register(new Components\HeroMinimalComponent());
    }
}
