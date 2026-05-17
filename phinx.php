<?php

$root = __DIR__;

return [
    'paths' => [
        'migrations' => $root . '/database/migrations',
        'seeds'      => $root . '/database/seeders',
    ],
    'environments' => [
        'default_migration_table' => 'kb_migrations',
        'default_environment'     => 'development',
        'development' => [
            'adapter'  => 'mysql',
            'host'     => getenv('DB_HOST')     ?: '127.0.0.1',
            'name'     => getenv('DB_DATABASE') ?: 'kbuilder',
            'user'     => getenv('DB_USERNAME') ?: 'root',
            'pass'     => getenv('DB_PASSWORD') ?: '',
            'port'     => getenv('DB_PORT')     ?: '3306',
            'charset'  => 'utf8mb4',
            'collation'=> 'utf8mb4_unicode_ci',
        ],
        'production' => [
            'adapter'  => 'mysql',
            'host'     => getenv('DB_HOST')     ?: '127.0.0.1',
            'name'     => getenv('DB_DATABASE') ?: 'kbuilder',
            'user'     => getenv('DB_USERNAME') ?: 'root',
            'pass'     => getenv('DB_PASSWORD') ?: '',
            'port'     => getenv('DB_PORT')     ?: '3306',
            'charset'  => 'utf8mb4',
            'collation'=> 'utf8mb4_unicode_ci',
        ],
    ],
    'version_order' => 'creation',
];
