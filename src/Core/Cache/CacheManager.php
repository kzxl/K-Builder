<?php

declare(strict_types=1);

namespace KBuilder\Core\Cache;

/**
 * Cache facade trung tâm. Chọn store theo config['driver'] (file|redis),
 * tự fallback về FileStore khi Redis không khả dụng.
 */
class CacheManager implements CacheStoreInterface
{
    private CacheStoreInterface $store;

    public function __construct(array $config)
    {
        $driver = $config['driver'] ?? 'file';
        $prefix = $config['prefix'] ?? 'kbuilder_';
        $ttl    = (int) ($config['file']['ttl'] ?? 3600);

        if ($driver === 'redis' && class_exists(\Predis\Client::class)) {
            try {
                $this->store = new RedisStore($config['redis'] ?? [], $ttl, $prefix);
                // Kiểm tra kết nối thực sự; nếu lỗi sẽ rơi xuống catch
                $this->store->has('__kb_ping__');
            } catch (\Throwable $e) {
                $this->store = new FileStore($config['file'] ?? []);
            }
        } else {
            $this->store = new FileStore($config['file'] ?? []);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store->get($key, $default);
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->store->set($key, $value, $ttl);
    }

    public function has(string $key): bool
    {
        return $this->store->has($key);
    }

    public function delete(string $key): bool
    {
        return $this->store->delete($key);
    }

    public function flush(): bool
    {
        return $this->store->flush();
    }

    /**
     * Lấy giá trị từ cache, nếu miss thì tính bằng $callback, lưu lại rồi trả về.
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $sentinel = '__kb_miss__';
        $cached = $this->store->get($key, $sentinel);

        if ($cached !== $sentinel) {
            return $cached;
        }

        $value = $callback();
        $this->store->set($key, $value, $ttl);

        return $value;
    }
}
