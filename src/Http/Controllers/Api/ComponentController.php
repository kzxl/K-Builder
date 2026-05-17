<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use KBuilder\Core\Component\ComponentRegistry;
use KBuilder\Core\Hook\HookSystem;
use Twig\Environment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ComponentController
{
    public function __construct(
        private readonly ComponentRegistry $registry,
        private readonly HookSystem        $hooks,
        private readonly Environment       $twig,
    ) {}

    /** GET /api/components — trả về danh sách component types theo group */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $list = $this->registry->toBuilderList();
        // Plugin có thể inject thêm metadata
        $list = $this->hooks->applyFilters('kbuilder/api_components_list', $list);

        return $this->json($response, ['success' => true, 'data' => $list]);
    }

    /** POST /api/components/preview — render section HTML cho live preview */
    public function preview(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body  = $request->getParsedBody() ?? [];
        $type  = $body['type'] ?? '';
        $props = $body['props'] ?? [];

        if (empty($type)) {
            return $this->json($response, ['success' => false, 'error' => 'type required'], 422);
        }

        if (!$this->registry->has($type)) {
            return $this->json($response, ['success' => false, 'error' => "Component type '{$type}' not found"], 404);
        }

        try {
            // Create a fake section array for render_component
            $section = [
                'type' => $type,
                'props' => $props,
            ];
            
            // Lấy extension ComponentExtension từ Twig để render
            $ext = $this->twig->getExtension(\KBuilder\Core\Twig\ComponentExtension::class);
            $html = $ext->renderComponent($section);

            return $this->json($response, ['success' => true, 'html' => $html]);
        } catch (\Throwable $e) {
            return $this->json($response, ['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
