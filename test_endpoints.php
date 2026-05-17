<?php
require 'vendor/autoload.php';
use Slim\Psr7\Factory\ServerRequestFactory;

$app = KBuilder\Core\Application::create();

function testEndpoint($app, $method, $uri, $body = null, $token = null) {
    $request = (new ServerRequestFactory())->createServerRequest($method, 'http://localhost' . $uri);
    if ($body) {
        $request = $request->withParsedBody($body);
        $request = $request->withHeader('Content-Type', 'application/json');
    }
    if ($token) {
        $request = $request->withHeader('Authorization', 'Bearer ' . $token);
    }
    
    $_SERVER['SCRIPT_NAME'] = '/kbuilder/public/index.php';
    $_SERVER['REQUEST_URI'] = $uri;
    
    $response = $app->handle($request);
    echo "[$method] $uri -> " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() >= 400) {
        echo (string)$response->getBody() . "\n\n";
    }
    return $response;
}

echo "1. Login...\n";
$loginRes = testEndpoint($app, 'POST', '/kbuilder/public/api/auth/login', [
    'email' => 'admin@kbuilder.local',
    'password' => 'Admin@12345'
]);

$token = null;
if ($loginRes->getStatusCode() === 200) {
    $data = json_decode((string)$loginRes->getBody(), true);
    $token = $data['data']['access_token'] ?? null;
    echo "Login SUCCESS. Token obtained.\n\n";
} else {
    die("Cannot test further without login.\n");
}

echo "2. Fetch Sites...\n";
testEndpoint($app, 'GET', '/kbuilder/public/api/sites', null, $token);

echo "3. Fetch Pages...\n";
testEndpoint($app, 'GET', '/kbuilder/public/api/pages', null, $token);

echo "4. Fetch Components...\n";
testEndpoint($app, 'GET', '/kbuilder/public/api/components', null, $token);

echo "5. Fetch Media...\n";
testEndpoint($app, 'GET', '/kbuilder/public/api/media', null, $token);

echo "6. Fetch Plugins...\n";
testEndpoint($app, 'GET', '/kbuilder/public/api/plugins', null, $token);

echo "\nAll core APIs tested.\n";
