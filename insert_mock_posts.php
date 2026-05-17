<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'kbuilder',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();

$now = date('Y-m-d H:i:s');

// Clear existing posts safely
Capsule::statement('SET FOREIGN_KEY_CHECKS=0;');
Capsule::table('kb_posts')->truncate();
Capsule::statement('SET FOREIGN_KEY_CHECKS=1;');

$posts = [
    [
        'site_id' => 1,
        'type' => 'post',
        'title' => 'Cập nhật tính năng Nested Layouts',
        'slug' => 'cap-nhat-tinh-nang-nested-layouts',
        'content' => 'Tính năng Nested Layouts cho phép người dùng lồng ghép các khối component vào nhau dễ dàng.',
        'excerpt' => 'Tính năng Nested Layouts vừa ra mắt.',
        'status' => 'published',
        'author_id' => 1,
        'published_at' => $now,
        'created_at' => $now,
    ],
    [
        'site_id' => 1,
        'type' => 'post',
        'title' => 'Hướng dẫn sử dụng Theme Customizer',
        'slug' => 'huong-dan-su-dung-theme-customizer',
        'content' => 'Theme Customizer giúp bạn tùy chỉnh giao diện toàn trang web chỉ với vài click.',
        'excerpt' => 'Theme Customizer giúp bạn chỉnh màu sắc.',
        'status' => 'published',
        'author_id' => 1,
        'published_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
    ],
    [
        'site_id' => 1,
        'type' => 'product',
        'title' => 'Gói Hosting Pro',
        'slug' => 'goi-hosting-pro',
        'content' => 'Gói Hosting Pro cấu hình cao, phù hợp cho doanh nghiệp lớn.',
        'excerpt' => 'Gói Hosting mạnh mẽ.',
        'status' => 'published',
        'author_id' => 1,
        'published_at' => $now,
        'created_at' => $now,
    ]
];

Capsule::table('kb_posts')->insert($posts);

echo "Mock posts inserted successfully!";
