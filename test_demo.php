<?php
require __DIR__ . '/vendor/autoload.php';
define('KB_ROOT', __DIR__);

$app = KBuilder\Core\Application::create();

use Illuminate\Database\Capsule\Manager as DB;

$siteId = 1;

try {
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

    echo "OK";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
