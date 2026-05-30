<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;

class MenuController
{
    private function getSiteId(ServerRequestInterface $request): int
    {
        return (int) ($request->getAttribute('auth_site_id') ?? 1);
    }

    /** GET /api/menus */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $siteId = $this->getSiteId($request);
        $menus = DB::table('menus')->where('site_id', $siteId)->get();
        return $this->json($response, ['success' => true, 'data' => $menus]);
    }

    /** GET /api/menus/{id} */
    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = $args['id'];
        $menu = DB::table('menus')->where('id', $id)->first();
        if (!$menu) {
            return $this->json($response, ['success' => false, 'error' => 'Menu not found'], 404);
        }

        $items = DB::table('menu_items')
            ->where('menu_id', $id)
            ->orderBy('sort_order', 'asc')
            ->get();

        $menu->items = $items;
        return $this->json($response, ['success' => true, 'data' => $menu]);
    }

    /** POST /api/menus */
    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];
        $siteId = $this->getSiteId($request);

        $id = DB::table('menus')->insertGetId([
            'site_id' => $siteId,
            'name' => $body['name'] ?? 'New Menu',
            'location' => $body['location'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->json($response, ['success' => true, 'data' => ['id' => $id]]);
    }

    /** PUT /api/menus/{id} */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = $args['id'];
        $body = $request->getParsedBody() ?? [];
        
        DB::beginTransaction();
        try {
            DB::table('menus')->where('id', $id)->update([
                'name' => $body['name'] ?? 'Menu',
                'location' => $body['location'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Xóa items cũ và chèn mới (cách đơn giản nhất cho menu builder)
            if (isset($body['items']) && is_array($body['items'])) {
                DB::table('menu_items')->where('menu_id', $id)->delete();
                
                $itemsToInsert = [];
                foreach ($body['items'] as $index => $item) {
                    $itemsToInsert[] = [
                        'menu_id' => $id,
                        'parent_id' => $item['parent_id'] ?? null,
                        'label' => $item['label'] ?? 'Item',
                        'type' => $item['type'] ?? 'url',
                        'target_id' => $item['target_id'] ?? null,
                        'url' => $item['url'] ?? '#',
                        'target' => $item['target'] ?? '_self',
                        'sort_order' => $index,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }
                
                if (!empty($itemsToInsert)) {
                    DB::table('menu_items')->insert($itemsToInsert);
                }
            }
            
            DB::commit();
            return $this->json($response, ['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->json($response, ['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /** DELETE /api/menus/{id} */
    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        DB::table('menus')->where('id', $args['id'])->delete();
        return $this->json($response, ['success' => true]);
    }

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
