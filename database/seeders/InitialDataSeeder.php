<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seed dữ liệu mặc định: roles, permissions, super admin user, default theme, default site.
 */
class InitialDataSeeder extends AbstractSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // ── Roles ──────────────────────────────────────────────────────
        $this->table('kb_roles')->insert([
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Toàn quyền hệ thống', 'is_system' => true,  'created_at' => $now],
            ['name' => 'Admin',       'slug' => 'admin',       'description' => 'Quản trị site',       'is_system' => true,  'created_at' => $now],
            ['name' => 'Editor',      'slug' => 'editor',      'description' => 'Biên tập nội dung',   'is_system' => false, 'created_at' => $now],
            ['name' => 'Viewer',      'slug' => 'viewer',      'description' => 'Chỉ xem',             'is_system' => false, 'created_at' => $now],
        ])->saveData();

        // ── Permissions ────────────────────────────────────────────────
        $resources = [
            ['pages',    ['view', 'create', 'edit', 'delete', 'publish']],
            ['posts',    ['view', 'create', 'edit', 'delete', 'publish']],
            ['media',    ['view', 'upload', 'delete']],
            ['menus',    ['view', 'edit']],
            ['settings', ['view', 'edit']],
            ['users',    ['view', 'create', 'edit', 'delete']],
            ['plugins',  ['view', 'toggle']],
            ['sites',    ['view', 'create', 'edit', 'delete']],
            ['analytics',['view', 'export']],
            ['themes',   ['view', 'apply', 'edit']],
        ];

        $perms = [];
        foreach ($resources as [$resource, $actions]) {
            foreach ($actions as $action) {
                $perms[] = [
                    'name'        => ucfirst($resource) . ' — ' . ucfirst($action),
                    'slug'        => $resource . '.' . $action,
                    'resource'    => $resource,
                    'action'      => $action,
                    'is_system'   => true,
                    'created_at'  => $now,
                ];
            }
        }
        $this->table('kb_permissions')->insert($perms)->saveData();

        // ── Default theme ──────────────────────────────────────────────
        $this->table('kb_themes')->insert([[
            'slug'        => 'default',
            'name'        => 'KBuilder Default',
            'version'     => '1.0.0',
            'description' => 'Theme mặc định của KBuilder',
            'is_system'   => true,
            'config'      => json_encode([
                'colors' => [
                    'primary'    => '#2563EB',
                    'secondary'  => '#64748B',
                    'accent'     => '#F59E0B',
                    'background' => '#FFFFFF',
                    'text'       => '#1E293B',
                ],
                'typography' => [
                    'heading_font' => 'Inter',
                    'body_font'    => 'Inter',
                ],
                'spacing' => [
                    'section_padding' => '80px',
                    'container_width' => '1200px',
                ],
                'border_radius' => '8px',
            ]),
            'created_at'  => $now,
        ]])->saveData();

        // ── Super Admin user ───────────────────────────────────────────
        $this->table('kb_users')->insert([[
            'uuid'       => $this->generateUuid(),
            'name'       => 'Super Admin',
            'email'      => 'admin@kbuilder.local',
            'password'   => password_hash('Admin@12345', PASSWORD_BCRYPT),
            'status'     => 'active',
            'meta'       => json_encode(['locale' => 'vi', 'timezone' => 'Asia/Ho_Chi_Minh']),
            'created_at' => $now,
        ]])->saveData();

        // ── Default site ───────────────────────────────────────────────
        $this->table('kb_sites')->insert([[
            'uuid'       => $this->generateUuid(),
            'name'       => 'My First Site',
            'slug'       => 'default',
            'status'     => 'active',
            'plan'       => 'pro',
            'theme_id'   => 1,
            'created_by' => 1,
            'meta'       => json_encode([
                'logo'        => null,
                'favicon'     => null,
                'tagline'     => 'Powered by KBuilder',
                'email'       => 'contact@mysite.com',
            ]),
            'created_at' => $now,
        ]])->saveData();

        // ── Assign super_admin role to user, site ──────────────────────
        $this->table('kb_user_roles')->insert([
            ['user_id' => 1, 'role_id' => 1, 'created_at' => $now],
        ])->saveData();

        $this->table('kb_site_users')->insert([
            ['site_id' => 1, 'user_id' => 1, 'role_id' => 1, 'created_at' => $now],
        ])->saveData();

        // ── Register built-in plugins ──────────────────────────────────
        $plugins = [
            ['core-hero',      'Core Hero Sections',  '1.0.0', 'Hero section types'],
            ['core-text',      'Core Text Sections',  '1.0.0', 'Text & content sections'],
            ['core-image',     'Core Image Sections', '1.0.0', 'Image & gallery sections'],
            ['core-blog',      'Core Blog',           '1.0.0', 'Post & category management'],
            ['core-media',     'Core Media',          '1.0.0', 'Media library'],
            ['core-seo',       'Core SEO',            '1.0.0', 'SEO tools'],
            ['core-analytics', 'Core Analytics',      '1.0.0', 'Analytics tracker'],
        ];
        $pluginRows = [];
        foreach ($plugins as [$slug, $name, $version, $desc]) {
            $pluginRows[] = [
                'slug'         => $slug,
                'name'         => $name,
                'version'      => $version,
                'description'  => $desc,
                'is_active'    => true,
                'is_system'    => true,
                'installed_at' => $now,
            ];
        }
        $this->table('kb_plugins')->insert($pluginRows)->saveData();

        // ── Default site settings ──────────────────────────────────────
        $this->table('kb_site_settings')->insert([
            ['site_id' => 1, 'group' => 'general', 'key' => 'site_name',    'value' => 'My First Site', 'type' => 'string', 'is_autoload' => true],
            ['site_id' => 1, 'group' => 'general', 'key' => 'site_tagline', 'value' => 'Powered by KBuilder', 'type' => 'string', 'is_autoload' => true],
            ['site_id' => 1, 'group' => 'general', 'key' => 'homepage_slug', 'value' => 'trang-chu', 'type' => 'string', 'is_autoload' => true],
            ['site_id' => 1, 'group' => 'seo',     'key' => 'meta_robots',  'value' => 'index,follow', 'type' => 'string', 'is_autoload' => false],
        ])->saveData();

        // ── Default Landing Page ───────────────────────────────────────
        $defaultLayout = [
            [
                'id' => 'sec-hero',
                'type' => 'hero_minimal',
                'props' => [
                    'badge_text' => '🔥 KBuilder v1.0',
                    'title' => 'Nền tảng kiến tạo Website',
                    'highlight' => 'thế hệ mới',
                    'subtitle' => 'Xây dựng mọi thứ với giao diện kéo thả trực quan, siêu mượt và không cần viết code.',
                    'cta_text' => 'Bắt đầu ngay',
                    'cta_url' => '#features',
                    'cta2_text' => 'Tài liệu',
                    'cta2_url' => '/docs',
                    'bg_gradient' => true
                ]
            ],
            [
                'id' => 'sec-features',
                'type' => 'core_features',
                'props' => [
                    'title' => 'Tại sao chọn KBuilder?',
                    'subtitle' => 'Hàng loạt tính năng độc quyền giúp bạn triển khai website trong chớp mắt.',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'zap', 'title' => 'Siêu tốc độ', 'description' => 'Tối ưu hóa hiệu năng tới từng mili-giây, điểm SEO luôn đạt 100/100.'],
                        ['icon' => 'layout-template', 'title' => 'Kéo thả mượt mà', 'description' => 'Thiết kế layout tự do với bộ khung component chuẩn mực.'],
                        ['icon' => 'shield-check', 'title' => 'Bảo mật tuyệt đối', 'description' => 'An toàn dữ liệu với kiến trúc bảo mật tiên tiến nhất.']
                    ]
                ]
            ],
            [
                'id' => 'sec-faq',
                'type' => 'core_faq',
                'props' => [
                    'title' => 'Câu hỏi thường gặp',
                    'items' => [
                        ['question' => 'KBuilder có miễn phí không?', 'answer' => 'Có, KBuilder là mã nguồn mở và hoàn toàn miễn phí cho cộng đồng.'],
                        ['question' => 'Tôi có thể tự tạo Plugin mới không?', 'answer' => 'Rất dễ dàng! KBuilder được thiết kế với kiến trúc Plugin-driven, bạn có thể tự viết Plugin chỉ với vài dòng code PHP.']
                    ]
                ]
            ],
            [
                'id' => 'sec-cta',
                'type' => 'core_button',
                'props' => [
                    'text' => 'Tham gia Cộng đồng ngay',
                    'url' => 'https://github.com/kbuilder',
                    'style' => 'primary',
                    'size' => 'lg',
                    'alignment' => 'center'
                ]
            ]
        ];

        $this->table('kb_pages')->insert([
            [
                'site_id' => 1,
                'title' => 'Trang Chủ',
                'slug' => 'trang-chu',
                'status' => 'published',
                'layout' => json_encode($defaultLayout, JSON_UNESCAPED_UNICODE),
                'seo' => json_encode(['title' => 'Trang Chủ - KBuilder', 'description' => 'KBuilder Landing Page']),
                'author_id' => 1,
                'published_at' => $now,
                'created_at' => $now,
            ]
        ])->saveData();
    }

    private function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
