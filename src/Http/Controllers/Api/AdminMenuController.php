<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use KBuilder\Core\Admin\AdminMenuRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminMenuController
{
    public function __construct(private readonly AdminMenuRegistry $registry) {}

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $menus = $this->registry->toArray();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $menus
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
