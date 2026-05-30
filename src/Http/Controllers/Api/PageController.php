<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use KBuilder\Core\Cache\CacheManager;
use KBuilder\Http\Validation\Validator;
use Respect\Validation\Validator as v;

class PageController
{
    public function __construct(
        private readonly CacheManager $cache
    ) {}

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    /** Xóa cache HTML của một trang theo slug (kèm sitemap vì danh sách URL có thể đổi). */
    private function forgetPageCache(?string $slug): void
    {
        if ($slug) {
            $this->cache->delete('page_html:' . $slug);
        }
        $this->cache->delete('sitemap_xml');
    }

    private function getSiteId(ServerRequestInterface $request): int
    {
        // Default site_id = 1 cho đến khi có UI chọn site
        return (int) ($request->getAttribute('auth_site_id') ?? 1);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $siteId = $this->getSiteId($request);
        $pages = DB::table('pages')
            ->where('site_id', $siteId)
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'title', 'slug', 'status', 'published_at', 'created_at', 'updated_at']);

        return $this->json($response, ['success' => true, 'data' => $pages]);
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $siteId = $this->getSiteId($request);
        $authorId = (int) $request->getAttribute('auth_user_id');
        $body = $request->getParsedBody() ?? [];

        Validator::validate($body, [
            'title' => v::notEmpty()->stringType()->length(1, 255),
            'slug'  => v::notEmpty()->slug(),
        ]);

        // Check unique slug per site
        $exists = DB::table('pages')
            ->where('site_id', $siteId)
            ->where('slug', $body['slug'])
            ->whereNull('deleted_at')
            ->exists();
            
        if ($exists) {
            return $this->json($response, ['success' => false, 'error' => 'Slug đã tồn tại'], 409);
        }

        $now = date('Y-m-d H:i:s');
        $id = DB::table('pages')->insertGetId([
            'site_id' => $siteId,
            'title' => $body['title'],
            'slug' => $body['slug'],
            'status' => $body['status'] ?? 'draft',
            'layout' => isset($body['layout']) ? json_encode($body['layout']) : '[]',
            'seo' => isset($body['seo']) ? json_encode($body['seo']) : null,
            'author_id' => $authorId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->json($response, ['success' => true, 'id' => $id]);
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);

        $page = DB::table('pages')
            ->where('id', $id)
            ->where('site_id', $siteId)
            ->whereNull('deleted_at')
            ->first();

        if (!$page) {
            return $this->json($response, ['success' => false, 'error' => 'Page not found'], 404);
        }

        $page->layout = $page->layout ? json_decode($page->layout, true) : [];
        $page->seo = $page->seo ? json_decode($page->seo, true) : [];
        $page->settings = $page->settings ? json_decode($page->settings, true) : [];

        return $this->json($response, ['success' => true, 'data' => $page]);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);
        $body = $request->getParsedBody() ?? [];

        $page = DB::table('pages')->where('id', $id)->where('site_id', $siteId)->first();
        if (!$page) {
            return $this->json($response, ['success' => false, 'error' => 'Page not found'], 404);
        }

        $updateData = ['updated_at' => date('Y-m-d H:i:s')];
        if (isset($body['title'])) $updateData['title'] = $body['title'];
        if (isset($body['slug'])) $updateData['slug'] = $body['slug'];
        if (isset($body['status'])) $updateData['status'] = $body['status'];
        
        // Cập nhật Layout (JSON)
        if (isset($body['layout'])) {
            $updateData['layout'] = json_encode($body['layout']);
            
            // Lưu Revision tự động khi có cập nhật layout từ Builder
            $authorId = (int) $request->getAttribute('auth_user_id');
            DB::table('page_revisions')->insert([
                'page_id' => $id,
                'layout' => json_encode($body['layout']),
                'seo' => $page->seo, // keep old seo for revision
                'author_id' => $authorId,
                'created_at' => date('Y-m-d H:i:s'),
                'note' => 'Auto-save layout'
            ]);
        }
        if (isset($body['seo'])) $updateData['seo'] = json_encode($body['seo']);

        DB::table('pages')->where('id', $id)->update($updateData);

        // Invalidate cache (slug cũ + slug mới nếu đổi)
        $this->forgetPageCache($page->slug);
        if (isset($body['slug'])) {
            $this->forgetPageCache($body['slug']);
        }

        return $this->json($response, ['success' => true]);
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);

        $page = DB::table('pages')->where('id', $id)->where('site_id', $siteId)->first();

        // Soft delete
        DB::table('pages')
            ->where('id', $id)
            ->where('site_id', $siteId)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        if ($page) {
            $this->forgetPageCache($page->slug);
        }

        return $this->json($response, ['success' => true]);
    }

    public function publish(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);

        DB::table('pages')
            ->where('id', $id)
            ->where('site_id', $siteId)
            ->update([
                'status' => 'published',
                'published_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        $page = DB::table('pages')->where('id', $id)->first();
        if ($page) {
            $this->forgetPageCache($page->slug);
        }

        return $this->json($response, ['success' => true]);
    }

    public function revisions(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $revisions = DB::table('page_revisions')
            ->where('page_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['id', 'author_id', 'note', 'created_at']);
            
        return $this->json($response, ['success' => true, 'data' => $revisions]);
    }

    public function restoreRevision(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id    = (int) $args['id'];
        $revId = (int) $args['revId'];
        $siteId = $this->getSiteId($request);
        $authorId = (int) $request->getAttribute('auth_user_id');

        $page = DB::table('pages')
            ->where('id', $id)
            ->where('site_id', $siteId)
            ->whereNull('deleted_at')
            ->first();

        if (!$page) {
            return $this->json($response, ['success' => false, 'error' => 'Page not found'], 404);
        }

        $revision = DB::table('page_revisions')
            ->where('id', $revId)
            ->where('page_id', $id)
            ->first();

        if (!$revision) {
            return $this->json($response, ['success' => false, 'error' => 'Revision not found'], 404);
        }

        $now = date('Y-m-d H:i:s');

        DB::beginTransaction();
        try {
            // Lưu snapshot trạng thái hiện tại trước khi phục hồi (để có thể undo)
            DB::table('page_revisions')->insert([
                'page_id'    => $id,
                'layout'     => $page->layout,
                'seo'        => $page->seo,
                'author_id'  => $authorId,
                'created_at' => $now,
                'note'       => 'Snapshot trước khi phục hồi revision #' . $revId,
            ]);

            // Phục hồi layout/seo từ revision được chọn
            DB::table('pages')->where('id', $id)->update([
                'layout'     => $revision->layout,
                'seo'        => $revision->seo,
                'updated_at' => $now,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->json($response, ['success' => false, 'error' => $e->getMessage()], 500);
        }

        $this->forgetPageCache($page->slug);

        return $this->json($response, [
            'success' => true,
            'message' => 'Đã phục hồi phiên bản #' . $revId,
            'layout'  => $revision->layout ? json_decode($revision->layout, true) : [],
        ]);
    }

    public function duplicate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $siteId = $this->getSiteId($request);
        $authorId = (int) $request->getAttribute('auth_user_id');

        $page = DB::table('pages')
            ->where('id', $id)
            ->where('site_id', $siteId)
            ->whereNull('deleted_at')
            ->first();

        if (!$page) {
            return $this->json($response, ['success' => false, 'error' => 'Page not found'], 404);
        }

        $now = date('Y-m-d H:i:s');
        $newSlug = $page->slug . '-copy-' . time();
        $newTitle = $page->title . ' (Copy)';

        $newId = DB::table('pages')->insertGetId([
            'site_id' => $siteId,
            'title' => $newTitle,
            'slug' => $newSlug,
            'status' => 'draft', // Always duplicate as draft
            'layout' => $page->layout,
            'seo' => $page->seo,
            'author_id' => $authorId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->json($response, ['success' => true, 'id' => $newId, 'slug' => $newSlug, 'title' => $newTitle]);
    }
}
