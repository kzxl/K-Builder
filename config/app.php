<?php

return [
    'name'    => $_ENV['APP_NAME'] ?? 'KBuilder',
    'env'     => $_ENV['APP_ENV'] ?? 'production',
    'debug'   => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'     => $_ENV['APP_URL'] ?? 'http://localhost',
    'key'     => $_ENV['APP_KEY'] ?? '',

    'plugins_path' => KB_ROOT . '/plugins',
    'storage_path' => KB_ROOT . '/storage',
    'cache_path'   => KB_ROOT . '/storage/cache',
    'logs_path'    => KB_ROOT . '/storage/logs',

    'admin_dist' => KB_ROOT . '/' . ($_ENV['ADMIN_DIST_PATH'] ?? 'apps/admin/dist'),
];
