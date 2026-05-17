<?php
require 'public/index.php';
use Illuminate\Database\Capsule\Manager as DB;

$plugins = [
    'kb-analytics' => 'Analytics & Tracking',
    'kb-seo-manager' => 'SEO Manager',
    'kb-contact-manager' => 'Contact Manager',
    'kb-security' => 'Security & Rate Limiting'
];

foreach ($plugins as $slug => $name) {
    $exists = DB::table('plugins')->where('slug', $slug)->exists();
    if (!$exists) {
        DB::table('plugins')->insert([
            'slug' => $slug,
            'name' => $name,
            'version' => '1.0.0',
            'is_active' => 1,
            'is_system' => 0,
            'installed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        echo "Installed $slug\n";
    }
}
echo "Done.\n";
