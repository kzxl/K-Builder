<?php
require 'vendor/autoload.php';

$app = KBuilder\Core\Application::create();

// Lấy ContentTypeRegistry từ container
$container = $app->getContainer();
$registry = $container->get(KBuilder\Core\Content\ContentTypeRegistry::class);

// Thử đăng ký 1 post type mới
$registry->registerPostType('product', [
    'label' => 'Sản phẩm',
    'icon' => 'Package',
    'taxonomies' => ['product_cat']
]);
$registry->registerTaxonomy('product_cat', [
    'label' => 'Danh mục Sản phẩm',
    'hierarchical' => true
]);

$_SERVER['SCRIPT_NAME'] = '/kbuilder/public/index.php';
$_SERVER['REQUEST_URI'] = '/kbuilder/public/api/content-types';

// Gọi API test
$request = (new Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('GET', '/kbuilder/public/api/content-types');
$response = $app->handle($request);

echo "Content Types:\n";
echo (string)$response->getBody() . "\n";
