<?php

declare(strict_types=1);

namespace KBuilder\Core\Cache;

/**
 * Cache lưu trên filesystem. Mỗi key là một file serialize chứa expiry + payload.
 */
class FileStore implements CacheStoreInterface
{
    private string $path;
    private int $defaultTtl;

    public function __construct(array $config)
    {
        $this->path = rtrim($config['path'] ?? sys_get_temp_dir(), '/\\');
        $this->defaultTtl = (int) ($config['ttl'] ?? 3600);

        if (!is_dir($this->path)) {
            @mkdir($this->path, 0775, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->filePath($key);
        if (!is_file($file)) {
            return $default;
        }

        $raw = @file_get_contents($file);
        if ($raw === false) {
            return $default;
        }

        $data = @unserialize($raw);
        if (!is_array($data) || !array_key_exists('expires', $data)) {
            return $default;
        }

        // 0 = không hết hạn
        if ($data['expires'] !== 0 && $data['expires'] < time()) {
            @unlink($file);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $expires = $ttl <= 0 ? 0 : time() + $ttl;

        $payload = serialize(['expires' => $expires, 'value' => $value]);

        return @file_put_contents($this->filePath($key), $payload, LOCK_EX) !== false;
    }

    public function has(string $key): bool
    {
        return $this->get($key, '__kb_miss__') !== '__kb_miss__';
    }

    public function delete(string $key): bool
    {
        $file = $this->filePath($key);
        return !is_file($file) || @unlink($file);
    }

    public function flush(): bool
    {
        foreach (glob($this->path . '/kb_*.cache') ?: [] as $file) {
            @unlink($file);
        }
        return true;
    }

    private function filePath(string $key): string
    {
        return $this->path . '/kb_' . sha1($key) . '.cache';
    }
}
