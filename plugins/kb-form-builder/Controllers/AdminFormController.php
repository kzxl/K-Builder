<?php

declare(strict_types=1);

namespace KBuilder\Plugins\KbFormBuilder\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Quản lý submission của Form Builder (admin, JWT-protected).
 */
class AdminFormController
{
    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    /** GET /api/admin/form-entries */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $q = $request->getQueryParams();
        $page  = max(1, (int) ($q['page'] ?? 1));
        $limit = min(100, max(1, (int) ($q['limit'] ?? 15)));
        $formKey = trim($q['form_key'] ?? '');

        $query = DB::table('form_entries');
        if ($formKey !== '') {
            $query->where('form_key', $formKey);
        }

        $total = $query->count();
        $items = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->data = $row->data ? json_decode($row->data, true) : [];
                return $row;
            });

        return $this->json($response, [
            'success' => true,
            'data' => [
                'items' => $items,
                'pagination' => [
                    'total' => $total,
                    'page'  => $page,
                    'limit' => $limit,
                    'pages' => (int) ceil($total / $limit),
                ],
            ],
        ]);
    }

    /** DELETE /api/admin/form-entries/{id} */
    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        DB::table('form_entries')->where('id', (int) $args['id'])->delete();
        return $this->json($response, ['success' => true]);
    }
}
