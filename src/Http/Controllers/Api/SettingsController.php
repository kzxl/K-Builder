<?php

declare(strict_types=1);

namespace KBuilder\Http\Controllers\Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;

class SettingsController
{
    /** GET /api/settings/{group} */
    public function getGroup(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $group = $args['group'];
        $settings = DB::table('site_settings')
            ->where('group', $group)
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        return $this->json($response, ['success' => true, 'data' => $settings]);
    }

    /** PUT /api/settings/{group} */
    public function updateGroup(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $group = $args['group'];
        $body = $request->getParsedBody() ?? [];

        foreach ($body as $key => $value) {
            DB::table('site_settings')->updateOrInsert(
                ['site_id' => 1, 'group' => $group, 'key' => $key],
                ['value' => $value, 'type' => 'string']
            );
        }

        return $this->json($response, ['success' => true]);
    }

    public function seedDemoData(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $siteId = 1;

        // Xóa sạch dữ liệu cũ
        DB::table('post_taxonomies')->delete();
        DB::table('posts')->where('site_id', $siteId)->delete();
        DB::table('taxonomies')->where('site_id', $siteId)->delete();
        DB::table('menu_items')->delete();
        DB::table('menus')->where('site_id', $siteId)->delete();
        DB::table('pages')->where('site_id', $siteId)->delete();

        $now = date('Y-m-d H:i:s');

        // Tạo Danh mục mẫu
        $catNewsId = DB::table('taxonomies')->insertGetId(['site_id' => $siteId, 'type' => 'category', 'name' => 'Tin tức', 'slug' => 'tin-tuc', 'created_at' => $now]);
        $catTechId = DB::table('taxonomies')->insertGetId(['site_id' => $siteId, 'type' => 'category', 'name' => 'Công nghệ', 'slug' => 'cong-nghe', 'created_at' => $now]);

        // Tạo Bài viết mẫu
        $posts = [
            ['title' => 'KBuilder 2026 chính thức ra mắt', 'slug' => 'kbuilder-2026', 'excerpt' => 'Phiên bản mới mang lại tốc độ đột phá.', 'cat' => $catNewsId],
            ['title' => 'Cách tạo giao diện lồng nhau', 'slug' => 'nested-layouts', 'excerpt' => 'Hướng dẫn sử dụng Core Columns plugin.', 'cat' => $catTechId],
            ['title' => 'Dữ liệu động trong CMS', 'slug' => 'dynamic-data', 'excerpt' => 'Kéo thả dữ liệu từ Database ra giao diện chỉ với 1 click.', 'cat' => $catTechId]
        ];

        foreach ($posts as $p) {
            $postId = DB::table('posts')->insertGetId([
                'site_id' => $siteId,
                'type' => 'post',
                'title' => $p['title'],
                'slug' => $p['slug'],
                'excerpt' => $p['excerpt'],
                'status' => 'published',
                'author_id' => 1,
                'created_at' => $now,
                'published_at' => $now
            ]);
            DB::table('post_taxonomies')->insert(['post_id' => $postId, 'taxonomy_id' => $p['cat']]);
        }

        // Tạo Trang chủ mẫu với layout phức tạp
        $homeLayout = [
            [
                'id' => 'hero_1',
                'type' => 'hero_split',
                'props' => [
                    'title' => 'Kiến tạo website chuyên nghiệp',
                    'subtitle' => 'KBuilder mang đến công cụ kéo thả trực quan kết hợp dữ liệu động sức mạnh từ PHP.',
                    'cta_text' => 'Bắt đầu ngay'
                ]
            ],
            [
                'id' => 'feat_1',
                'type' => 'core_features',
                'props' => [
                    'title' => 'Tính năng nổi bật',
                    'data_source' => ['type' => 'posts', 'limit' => 3]
                ]
            ],
            [
                'id' => 'col_1',
                'type' => 'core_columns',
                'props' => [
                    'layout' => '1-1',
                    'col1_children' => [
                        ['id' => 'txt_1', 'type' => 'core_text', 'props' => ['content' => '<h2>Sứ mệnh của chúng tôi</h2><p>Mang công nghệ đến mọi người.</p>']]
                    ],
                    'col2_children' => [
                        ['id' => 'btn_1', 'type' => 'core_button', 'props' => ['text' => 'Liên hệ ngay', 'url' => '/lien-he']]
                    ]
                ]
            ]
        ];

        DB::table('pages')->insert([
            ['site_id' => $siteId, 'title' => 'Trang chủ', 'slug' => 'home', 'status' => 'published', 'layout' => json_encode($homeLayout), 'author_id' => 1, 'created_at' => $now],
            ['site_id' => $siteId, 'title' => 'Liên hệ', 'slug' => 'lien-he', 'status' => 'published', 'layout' => '[]', 'author_id' => 1, 'created_at' => $now]
        ]);

        return $this->json($response, ['success' => true, 'message' => 'Đã khởi tạo dữ liệu mẫu!']);
    }

    public function exportSite(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $siteId = 1;
        $exportData = [
            'version' => '1.0',
            'exported_at' => date('Y-m-d H:i:s'),
            'data' => [
                'pages' => DB::table('pages')->where('site_id', $siteId)->get()->toArray(),
                'taxonomies' => DB::table('taxonomies')->where('site_id', $siteId)->get()->toArray(),
                'posts' => DB::table('posts')->where('site_id', $siteId)->get()->toArray(),
                'post_taxonomies' => DB::table('post_taxonomies')->get()->toArray(),
                'menus' => DB::table('menus')->where('site_id', $siteId)->get()->toArray(),
                'menu_items' => DB::table('menu_items')->get()->toArray(),
            ]
        ];

        $response->getBody()->write(json_encode($exportData, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Disposition', 'attachment; filename="kbuilder-export-' . date('Ymd-His') . '.json"');
    }

    public function importSite(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Giả lập nhận JSON từ request body
        $body = $request->getParsedBody() ?? [];
        if (empty($body['data']) || empty($body['version'])) {
            return $this->json($response, ['success' => false, 'error' => 'File import không hợp lệ'], 400);
        }

        $siteId = 1;
        
        DB::beginTransaction();
        try {
            // Xoá data cũ
            DB::table('post_taxonomies')->delete();
            DB::table('posts')->where('site_id', $siteId)->delete();
            DB::table('taxonomies')->where('site_id', $siteId)->delete();
            DB::table('menu_items')->delete();
            DB::table('menus')->where('site_id', $siteId)->delete();
            DB::table('pages')->where('site_id', $siteId)->delete();

            $data = $body['data'];
            
            // Insert data mới (cần map ID nều table có Auto Increment conflict, ở đây giả lập insert luôn ID cũ cho giống file)
            if (!empty($data['taxonomies'])) {
                $taxs = array_map(function($item) { return (array)$item; }, $data['taxonomies']);
                DB::table('taxonomies')->insert($taxs);
            }
            if (!empty($data['posts'])) {
                $posts = array_map(function($item) { return (array)$item; }, $data['posts']);
                DB::table('posts')->insert($posts);
            }
            if (!empty($data['post_taxonomies'])) {
                $pt = array_map(function($item) { return (array)$item; }, $data['post_taxonomies']);
                DB::table('post_taxonomies')->insert($pt);
            }
            if (!empty($data['pages'])) {
                $pages = array_map(function($item) { return (array)$item; }, $data['pages']);
                DB::table('pages')->insert($pages);
            }
            if (!empty($data['menus'])) {
                $menus = array_map(function($item) { return (array)$item; }, $data['menus']);
                DB::table('menus')->insert($menus);
            }
            if (!empty($data['menu_items'])) {
                $mi = array_map(function($item) { return (array)$item; }, $data['menu_items']);
                DB::table('menu_items')->insert($mi);
            }

            DB::commit();
            return $this->json($response, ['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->json($response, ['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
