<?php

declare(strict_types=1);

namespace KBuilder\Core\Cache;

/**
 * Hợp đồng chung cho mọi cache store (file, redis, ...).
 */
interface CacheStoreInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    public function has(string $key): bool;

    public function delete(string $key): bool;

    public function flush(): bool;
}
