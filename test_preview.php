<?php
define('KB_ROOT', __DIR__);
define('KB_VERSION', '1.0.0');

require KB_ROOT . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(KB_ROOT);
$dotenv->load();

$app = \KBuilder\Core\Application::create();
$container = $app->getContainer();

$componentController = $container->get(\KBuilder\Http\Controllers\Api\ComponentController::class);

$requestFactory = new \Slim\Psr7\Factory\ServerRequestFactory();
$request = $requestFactory->createServerRequest('POST', '/api/components/preview')
    ->withParsedBody([
        'type' => 'core_hero',
        'props' => [
            'title' => 'Test Preview',
            'subtitle' => 'Testing TWIG render'
        ]
    ]);

$responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
$response = $responseFactory->createResponse();

$result = $componentController->preview($request, $response);
echo (string)$result->getBody();
