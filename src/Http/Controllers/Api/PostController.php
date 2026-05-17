<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use KBuilder\Core\Content\ContentTypeRegistry;

class PostController
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
        $type = $queryParams['type'] ?? 'post';

        if (!$this->registry->hasPostType($type)) {
            return $this->json($response, ['success' => false, 'error' => 'Invalid post type'], 400);
        }

        $posts = DB::table('posts')
            ->where('site_id', $siteId)
            ->where('type', $type)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'slug', 'status', 'published_at', 'created_at']);

        // Attach categories
        foreach ($posts as $post) {
            $post->taxonomies = DB::table('post_taxonomies')
                ->join('taxonomies', 'post_taxonomies.taxonomy_id', '=', 'taxonomies.id')
                ->where('post_taxonomies.post_id', $post->id)
                ->pluck('taxonomies.name');
        }

        return $this->json($response, ['success' => true, 'data' => $posts]);
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $siteId = $this->getSiteId($request);
        $authorId = (int) $request->getAttribute('auth_user_id');
        $body = $request->getParsedBody() ?? [];

        if (empty($body['title']) || empty($body['slug'])) {
            return $this->json($response, ['success' => false, 'error' => 'Title and slug are required'], 422);
        }

        $exists = DB::table('posts')
            ->where('site_id', $siteId)
            ->where('slug', $body['slug'])
            ->whereNull('deleted_at')
            ->exists();
            
        if ($exists) {
            return $this->json($response, ['success' => false, 'error' => 'Slug đã tồn tại'], 409);
        }

        $now = date('Y-m-d H:i:s');
        $status = $body['status'] ?? 'draft';
        $type = $body['type'] ?? 'post';

        if (!$this->registry->hasPostType($type)) {
            return $this->json($response, ['success' => false, 'error' => 'Invalid post type'], 400);
        }

        $id = DB::table('posts')->insertGetId([
            'site_id' => $siteId,
            'type' => $type,
            'title' => $body['title'],
            'slug' => $body['slug'],
            'content' => $body['content'] ?? null,
            'excerpt' => $body['excerpt'] ?? null,
            'status' => $status,
            'featured_image_id' => $body['featured_image_id'] ?? null,
            'author_id' => $authorId,
            'published_at' => $status === 'published' ? $now : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (!empty($body['taxonomies']) && is_array($body['taxonomies'])) {
            $taxData = array_map(fn($tId) => ['post_id' => $id, 'taxonomy_id' => $tId], $body['taxonomies']);
            DB::table('post_taxonomies')->insert($taxData);
        }

        return $this->json($response, ['success' => true, 'id' => $id]);
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);

        $post = DB::table('posts')
            ->where('id', $id)
            ->where('site_id', $siteId)
            ->whereNull('deleted_at')
            ->first();

        if (!$post) {
            return $this->json($response, ['success' => false, 'error' => 'Post not found'], 404);
        }

        $post->taxonomies = DB::table('post_taxonomies')
            ->where('post_id', $post->id)
            ->pluck('taxonomy_id');

        return $this->json($response, ['success' => true, 'data' => $post]);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);
        $body = $request->getParsedBody() ?? [];

        $post = DB::table('posts')->where('id', $id)->where('site_id', $siteId)->first();
        if (!$post) {
            return $this->json($response, ['success' => false, 'error' => 'Post not found'], 404);
        }

        $updateData = ['updated_at' => date('Y-m-d H:i:s')];
        if (isset($body['title'])) $updateData['title'] = $body['title'];
        if (isset($body['slug'])) $updateData['slug'] = $body['slug'];
        if (isset($body['content'])) $updateData['content'] = $body['content'];
        if (isset($body['excerpt'])) $updateData['excerpt'] = $body['excerpt'];
        if (isset($body['featured_image_id'])) $updateData['featured_image_id'] = $body['featured_image_id'];
        
        if (isset($body['status'])) {
            $updateData['status'] = $body['status'];
            if ($body['status'] === 'published' && !$post->published_at) {
                $updateData['published_at'] = date('Y-m-d H:i:s');
            }
        }

        DB::table('posts')->where('id', $id)->update($updateData);

        if (isset($body['taxonomies']) && is_array($body['taxonomies'])) {
            DB::table('post_taxonomies')->where('post_id', $id)->delete();
            $taxData = array_map(fn($tId) => ['post_id' => $id, 'taxonomy_id' => $tId], $body['taxonomies']);
            if (!empty($taxData)) {
                DB::table('post_taxonomies')->insert($taxData);
            }
        }

        return $this->json($response, ['success' => true]);
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);

        DB::table('posts')
            ->where('id', $id)
            ->where('site_id', $siteId)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return $this->json($response, ['success' => true]);
    }
}
