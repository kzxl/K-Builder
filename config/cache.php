<?php

return [
    'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
    'prefix' => $_ENV['CACHE_PREFIX'] ?? 'kbuilder_',
    'file'   => [
        'path' => KB_ROOT . '/storage/cache',
        'ttl'  => 3600,
    ],
    'redis'  => [
        'host'     => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
        'port'     => (int) ($_ENV['REDIS_PORT'] ?? 6379),
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        'database' => 0,
    ],
];
