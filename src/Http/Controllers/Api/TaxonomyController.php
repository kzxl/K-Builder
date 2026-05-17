<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use KBuilder\Core\Content\ContentTypeRegistry;

class TaxonomyController
{
    public function __construct(
        private readonly ContentTypeRegistry $registry
    ) {}

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function getSiteId(ServerRequestInterface $request): int
    {
        return (int) ($request->getAttribute('auth_site_id') ?? 1);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $siteId = $this->getSiteId($request);
        $queryParams = $request->getQueryParams();
        $type = $queryParams['type'] ?? 'category';

        if (!$this->registry->getTaxonomy($type)) {
            return $this->json($response, ['success' => false, 'error' => 'Invalid taxonomy type'], 400);
        }

        $taxonomies = DB::table('taxonomies')
            ->where('site_id', $siteId)
            ->where('type', $type)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'slug', 'description', 'parent_id', 'status', 'created_at']);

        // Count posts per taxonomy
        foreach ($taxonomies as $tax) {
            $tax->post_count = DB::table('post_taxonomies')
                ->join('posts', 'post_taxonomies.post_id', '=', 'posts.id')
                ->where('post_taxonomies.taxonomy_id', $tax->id)
                ->whereNull('posts.deleted_at')
                ->count();
        }

        return $this->json($response, ['success' => true, 'data' => $taxonomies]);
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $siteId = $this->getSiteId($request);
        $body = $request->getParsedBody() ?? [];

        if (empty($body['name']) || empty($body['slug']) || empty($body['type'])) {
            return $this->json($response, ['success' => false, 'error' => 'Name, slug, and type are required'], 422);
        }

        $exists = DB::table('taxonomies')
            ->where('site_id', $siteId)
            ->where('type', $body['type'])
            ->where('slug', $body['slug'])
            ->exists();
            
        if ($exists) {
            return $this->json($response, ['success' => false, 'error' => 'Slug đã tồn tại trong loại danh mục này'], 409);
        }

        $now = date('Y-m-d H:i:s');
        $type = $body['type'] ?? 'category';

        if (!$this->registry->getTaxonomy($type)) {
            return $this->json($response, ['success' => false, 'error' => 'Invalid taxonomy type'], 400);
        }

        $id = DB::table('taxonomies')->insertGetId([
            'site_id' => $siteId,
            'type' => $type,
            'name' => $body['name'],
            'slug' => $body['slug'],
            'description' => $body['description'] ?? null,
            'parent_id' => $body['parent_id'] ?? null,
            'image_id' => $body['image_id'] ?? null,
            'status' => $body['status'] ?? 'published',
            'sort_order' => $body['sort_order'] ?? 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->json($response, ['success' => true, 'id' => $id]);
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);

        $tax = DB::table('taxonomies')->where('id', $id)->where('site_id', $siteId)->first();
        if (!$tax) {
            return $this->json($response, ['success' => false, 'error' => 'Taxonomy not found'], 404);
        }

        return $this->json($response, ['success' => true, 'data' => $tax]);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);
        $body = $request->getParsedBody() ?? [];

        $tax = DB::table('taxonomies')->where('id', $id)->where('site_id', $siteId)->first();
        if (!$tax) {
            return $this->json($response, ['success' => false, 'error' => 'Taxonomy not found'], 404);
        }

        $updateData = ['updated_at' => date('Y-m-d H:i:s')];
        if (isset($body['name'])) $updateData['name'] = $body['name'];
        if (isset($body['slug'])) $updateData['slug'] = $body['slug'];
        if (isset($body['description'])) $updateData['description'] = $body['description'];
        if (isset($body['parent_id'])) $updateData['parent_id'] = $body['parent_id'];
        if (isset($body['image_id'])) $updateData['image_id'] = $body['image_id'];
        if (isset($body['status'])) $updateData['status'] = $body['status'];
        if (isset($body['sort_order'])) $updateData['sort_order'] = $body['sort_order'];

        DB::table('taxonomies')->where('id', $id)->where('site_id', $siteId)->update($updateData);

        return $this->json($response, ['success' => true]);
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);

        // Delete post associations first
        DB::table('post_taxonomies')->where('taxonomy_id', $id)->delete();

        // Hard delete taxonomy
        DB::table('taxonomies')
            ->where('id', $id)
            ->where('site_id', $siteId)
            ->delete();

        return $this->json($response, ['success' => true]);
    }
}
