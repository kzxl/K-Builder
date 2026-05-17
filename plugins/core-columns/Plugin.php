<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreColumns;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Component\ComponentRegistry;

class Plugin extends AbstractPlugin
{
    public function getId(): string
    {
        return 'core-columns';
    }

    public function getName(): string
    {
        return 'Core Columns';
    }

    public function registerComponents(ComponentRegistry $registry): void
    {
        $registry->register(new Components\ColumnsComponent());
    }
}
