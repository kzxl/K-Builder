<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use KBuilder\Core\Content\ContentTypeRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ContentTypeController
{
    public function __construct(
        private readonly ContentTypeRegistry $registry
    ) {}

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = [
            'post_types' => $this->registry->getPostTypes(),
            'taxonomies' => $this->registry->getTaxonomies(),
        ];

        $response->getBody()->write(json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
