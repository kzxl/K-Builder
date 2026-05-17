<?php
define('KB_ROOT', __DIR__ . '/src');
require __DIR__ . '/vendor/autoload.php';

$container = (new \DI\ContainerBuilder())
    ->addDefinitions(__DIR__ . '/src/Core/container.php')
    ->build();

$registry = $container->get(\KBuilder\Core\Content\ContentTypeRegistry::class);

echo "Registered Post Types:\n";
print_r($registry->getPostTypes());

echo "\nRegistered Taxonomies:\n";
print_r($registry->getTaxonomies());
