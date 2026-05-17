<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbuilderCore;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Plugins\KbuilderCore\Components\HeroComponent;
use KBuilder\Plugins\KbuilderCore\Components\TextComponent;
use KBuilder\Plugins\KbuilderCore\Components\ContainerComponent;

class Plugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'kbuilder-core';
    }

    public function getName(): string
    {
        return 'KBuilder Core Component Plugin';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function registerComponents(ComponentRegistry $registry): void
    {
        $registry->register(new HeroComponent());
        $registry->register(new TextComponent());
        $registry->register(new ContainerComponent());
    }
}
