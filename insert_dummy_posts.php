<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Capsule\Manager as DB;

define('KB_ROOT', __DIR__);

$config = require KB_ROOT . '/config/database.php';
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => $config['driver'],
    'host'      => $config['host'],
    'port'      => $config['port'],
    'database'  => $config['database'],
    'username'  => $config['username'],
    'password'  => $config['password'],
    'charset'   => $config['charset'],
    'collation' => $config['collation'],
    'prefix'    => $config['prefix'],
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "Inserting dummy posts...\n";

$posts = [
    [
        'site_id' => 1,
        'type' => 'post',
        'title' => 'Khám phá kiến trúc hiện đại của KBuilder',
        'slug' => 'kham-pha-kien-truc-hien-dai',
        'excerpt' => 'Tìm hiểu cách hệ thống Plugin và Hook giúp KBuilder trở thành một CMS vô cùng mạnh mẽ.',
        'status' => 'published',
        'author_id' => 1,
        'created_at' => date('Y-m-d H:i:s'),
    ],
    [
        'site_id' => 1,
        'type' => 'post',
        'title' => 'Hướng dẫn tối ưu tốc độ cho website tĩnh',
        'slug' => 'huong-dan-toi-uu-toc-do',
        'excerpt' => 'Cách tận dụng Static Caching để đạt điểm PageSpeed tuyệt đối trên Google.',
        'status' => 'published',
        'author_id' => 1,
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
    ],
    [
        'site_id' => 1,
        'type' => 'post',
        'title' => 'Top 5 xu hướng thiết kế web năm 2026',
        'slug' => 'top-5-xu-huong-thiet-ke-web-2026',
        'excerpt' => 'Glassmorphism, Dark mode và Micro-interactions đang thống trị.',
        'status' => 'published',
        'author_id' => 1,
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
    ],
    [
        'site_id' => 1,
        'type' => 'post',
        'title' => 'Tầm quan trọng của dữ liệu động trong CMS',
        'slug' => 'tam-quan-trong-cua-du-lieu-dong',
        'excerpt' => 'Tại sao các Page Builder thông thường không đủ để xây dựng một website lớn?',
        'status' => 'published',
        'author_id' => 1,
        'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
    ]
];

foreach ($posts as $post) {
    try {
        DB::table('posts')->insert($post);
        echo "Inserted: {$post['title']}\n";
    } catch (\Exception $e) {
        echo "Failed to insert {$post['title']}: " . $e->getMessage() . "\n";
    }
}

echo "Done!\n";
