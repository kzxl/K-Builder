<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use KBuilder\Http\Validation\Validator;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;

class SiteController
{
    private const STATUSES = ['active', 'inactive', 'suspended'];

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function decode(object $site): object
    {
        $site->meta = isset($site->meta) && $site->meta ? json_decode($site->meta, true) : [];
        return $site;
    }

    /** GET /api/sites */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $sites = DB::table('sites')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $sites = $sites->map(fn ($s) => $this->decode($s));

        return $this->json($response, ['success' => true, 'data' => $sites]);
    }

    /** GET /api/sites/{id} */
    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $site = DB::table('sites')
            ->where('id', (int) $args['id'])
            ->whereNull('deleted_at')
            ->first();

        if (!$site) {
            return $this->json($response, ['success' => false, 'error' => 'Site not found'], 404);
        }

        return $this->json($response, ['success' => true, 'data' => $this->decode($site)]);
    }

    /** POST /api/sites */
    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];
        $authorId = (int) $request->getAttribute('auth_user_id');

        Validator::validate($body, [
            'name' => v::notEmpty()->stringType()->length(1, 255),
            'slug' => v::notEmpty()->slug(),
        ]);

        $name = trim($body['name']);
        $slug = trim($body['slug']);

        $status = in_array($body['status'] ?? 'active', self::STATUSES, true) ? $body['status'] : 'active';

        $exists = DB::table('sites')
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return $this->json($response, ['success' => false, 'error' => 'Slug đã tồn tại'], 409);
        }

        $now = date('Y-m-d H:i:s');
        $id = DB::table('sites')->insertGetId([
            'uuid'       => Uuid::uuid4()->toString(),
            'name'       => $name,
            'slug'       => $slug,
            'domain'     => $body['domain'] ?? null,
            'status'     => $status,
            'plan'       => $body['plan'] ?? 'free',
            'theme_id'   => isset($body['theme_id']) ? (int) $body['theme_id'] : null,
            'created_by' => $authorId,
            'meta'       => isset($body['meta']) ? json_encode($body['meta']) : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->json($response, ['success' => true, 'id' => $id], 201);
    }

    /** PUT /api/sites/{id} */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $body = $request->getParsedBody() ?? [];

        $site = DB::table('sites')->where('id', $id)->whereNull('deleted_at')->first();
        if (!$site) {
            return $this->json($response, ['success' => false, 'error' => 'Site not found'], 404);
        }

        if (isset($body['slug']) && $body['slug'] !== $site->slug) {
            $dup = DB::table('sites')
                ->where('slug', $body['slug'])
                ->where('id', '!=', $id)
                ->whereNull('deleted_at')
                ->exists();
            if ($dup) {
                return $this->json($response, ['success' => false, 'error' => 'Slug đã tồn tại'], 409);
            }
        }

        $updateData = ['updated_at' => date('Y-m-d H:i:s')];
        if (isset($body['name']))     $updateData['name']     = $body['name'];
        if (isset($body['slug']))     $updateData['slug']     = $body['slug'];
        if (isset($body['domain']))   $updateData['domain']   = $body['domain'];
        if (isset($body['plan']))     $updateData['plan']     = $body['plan'];
        if (isset($body['theme_id'])) $updateData['theme_id'] = (int) $body['theme_id'];
        if (isset($body['meta']))     $updateData['meta']     = json_encode($body['meta']);
        if (isset($body['status']) && in_array($body['status'], self::STATUSES, true)) {
            $updateData['status'] = $body['status'];
        }

        DB::table('sites')->where('id', $id)->update($updateData);

        return $this->json($response, ['success' => true]);
    }

    /** DELETE /api/sites/{id} */
    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];

        // Không cho xóa site cuối cùng còn lại
        $remaining = DB::table('sites')->whereNull('deleted_at')->count();
        if ($remaining <= 1) {
            return $this->json($response, ['success' => false, 'error' => 'Không thể xóa site cuối cùng'], 409);
        }

        DB::table('sites')
            ->where('id', $id)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return $this->json($response, ['success' => true]);
    }
}
