<?php
require 'public/index.php';
$registry = $app->getContainer()->get(\KBuilder\Core\Plugin\PluginRegistry::class);
$controller = new \KBuilder\Http\Controllers\Api\PluginController($registry);
$request = (new \Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('GET', '/api/plugins');
$response = new \Slim\Psr7\Response();
try {
    $res = $controller->index($request, $response);
    echo $res->getBody();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
