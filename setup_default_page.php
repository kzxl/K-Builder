<?php
require 'vendor/autoload.php';

use KBuilder\Core\Application;
use Illuminate\Database\Capsule\Manager as DB;

define('KB_ROOT', __DIR__);

$dotenv = Dotenv\Dotenv::createImmutable(KB_ROOT);
$dotenv->load();

$app = Application::create();

$now = date('Y-m-d H:i:s');

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

// Kiểm tra xem trang chủ đã có chưa
$pageExists = DB::table('pages')->where('slug', 'trang-chu')->exists();

if (!$pageExists) {
    DB::table('pages')->insert([
        'site_id' => 1,
        'title' => 'Trang Chủ',
        'slug' => 'trang-chu',
        'status' => 'published',
        'layout' => json_encode($defaultLayout, JSON_UNESCAPED_UNICODE),
        'seo' => json_encode(['title' => 'Trang Chủ - KBuilder', 'description' => 'KBuilder Landing Page']),
        'author_id' => 1,
        'published_at' => $now,
        'created_at' => $now,
    ]);
    echo "Đã tạo trang chủ mặc định (slug: trang-chu).\n";
} else {
    echo "Trang chủ mặc định đã tồn tại. Bỏ qua.\n";
}

// Cập nhật settings
$settingExists = DB::table('site_settings')->where('key', 'homepage_slug')->exists();
if (!$settingExists) {
    DB::table('site_settings')->insert([
        'site_id' => 1,
        'group' => 'general',
        'key' => 'homepage_slug',
        'value' => 'trang-chu',
        'type' => 'string',
        'is_autoload' => true
    ]);
    echo "Đã thiết lập homepage_slug trong site_settings.\n";
} else {
    echo "Cấu hình homepage_slug đã tồn tại.\n";
}

echo "Hoàn tất!\n";
