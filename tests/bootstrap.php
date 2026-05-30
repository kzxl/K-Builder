<?php

declare(strict_types=1);

// Bootstrap cho PHPUnit: nạp Composer autoload và định nghĩa hằng số lõi.

if (!defined('KB_ROOT')) {
    define('KB_ROOT', dirname(__DIR__));
}
if (!defined('KB_VERSION')) {
    define('KB_VERSION', 'test');
}

require dirname(__DIR__) . '/vendor/autoload.php';

// Nạp các plugin component dùng trong test (không nằm trong PSR-4 autoload)
require_once dirname(__DIR__) . '/plugins/core-blocks/Components/VideoComponent.php';
require_once dirname(__DIR__) . '/plugins/core-blocks/Components/PricingComponent.php';
require_once dirname(__DIR__) . '/plugins/core-blocks/Components/GalleryComponent.php';
require_once dirname(__DIR__) . '/plugins/kb-form-builder/Components/FormBuilderComponent.php';
