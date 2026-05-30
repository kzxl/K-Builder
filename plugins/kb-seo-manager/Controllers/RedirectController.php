<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbSeoManager\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * CRUD quản lý redirect (chuyển hướng URL) cho SEO.
 * Dùng tên bảng không prefix 'redirects' → Capsule tự thêm 'kb_'.
 */
class RedirectController
{
    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function siteId(ServerRequestInterface $request): int
    {
        return (int) ($request->getAttribute('auth_site_id') ?? 1);
    }

    /** GET /api/admin/redirects */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $rows = DB::table('redirects')
            ->where('site_id', $this->siteId($request))
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->json($response, ['success' => true, 'data' => $rows]);
    }

    /** POST /api/admin/redirects */
    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];
        $from = trim($body['from_url'] ?? '');
        $to   = trim($body['to_url'] ?? '');

        if ($from === '' || $to === '') {
            return $this->json($response, ['success' => false, 'error' => 'from_url và to_url là bắt buộc'], 422);
        }

        $code = (int) ($body['code'] ?? 301);
        if (!in_array($code, [301, 302, 307, 308], true)) {
            $code = 301;
        }

        $now = date('Y-m-d H:i:s');
        $id = DB::table('redirects')->insertGetId([
            'site_id'    => $this->siteId($request),
            'from_url'   => $from,
            'to_url'     => $to,
            'code'       => $code,
            'is_active'  => (bool) ($body['is_active'] ?? true),
            'hit_count'  => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->json($response, ['success' => true, 'id' => $id], 201);
    }

    /** PUT /api/admin/redirects/{id} */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $body = $request->getParsedBody() ?? [];

        $exists = DB::table('redirects')->where('id', $id)->exists();
        if (!$exists) {
            return $this->json($response, ['success' => false, 'error' => 'Redirect not found'], 404);
        }

        $data = ['updated_at' => date('Y-m-d H:i:s')];
        if (isset($body['from_url']))  $data['from_url']  = trim($body['from_url']);
        if (isset($body['to_url']))    $data['to_url']    = trim($body['to_url']);
        if (isset($body['is_active'])) $data['is_active'] = (bool) $body['is_active'];
        if (isset($body['code'])) {
            $code = (int) $body['code'];
            if (in_array($code, [301, 302, 307, 308], true)) {
                $data['code'] = $code;
            }
        }

        DB::table('redirects')->where('id', $id)->update($data);

        return $this->json($response, ['success' => true]);
    }

    /** DELETE /api/admin/redirects/{id} */
    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        DB::table('redirects')->where('id', (int) $args['id'])->delete();
        return $this->json($response, ['success' => true]);
    }
}
