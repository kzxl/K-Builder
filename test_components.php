<?php
define('KB_ROOT', __DIR__);
define('KB_VERSION', '1.0.0');

require KB_ROOT . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(KB_ROOT);
$dotenv->load();

// Bootstrap application
$app = \KBuilder\Core\Application::create();

// The components should be loaded now
$registry = $app->getContainer()->get(\KBuilder\Core\Component\ComponentRegistry::class);
echo "Registered Components:\n";
foreach ($registry->all() as $comp) {
    echo "- " . $comp->getType() . " (from " . get_class($comp) . ")\n";
}
