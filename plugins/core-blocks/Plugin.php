<?php

declare(strict_types=1);

namespace KBuilder\Plugins\CoreBlocks;

use KBuilder\Core\Plugin\AbstractPlugin;
use KBuilder\Core\Component\ComponentRegistry;

class Plugin extends AbstractPlugin
{
    public function getId(): string { return 'core-blocks'; }
    public function getName(): string { return 'Core Blocks (Video, Pricing, Gallery)'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDescription(): string { return 'Bộ component bổ sung: Video, Bảng giá và Thư viện ảnh.'; }

    public function registerComponents(ComponentRegistry $registry): void
    {
        require_once __DIR__ . '/Components/VideoComponent.php';
        require_once __DIR__ . '/Components/PricingComponent.php';
        require_once __DIR__ . '/Components/GalleryComponent.php';

        $registry->register(new Components\VideoComponent());
        $registry->register(new Components\PricingComponent());
        $registry->register(new Components\GalleryComponent());
    }
}
