<?php

declare(strict_types=1);

namespace KBuilder\Core\Cache;

use Predis\Client;

/**
 * Cache dùng Redis qua Predis. Giá trị được serialize bằng PHP serialize().
 */
class RedisStore implements CacheStoreInterface
{
    private Client $client;
    private int $defaultTtl;
    private string $prefix;

    public function __construct(array $config, int $defaultTtl, string $prefix)
    {
        $this->defaultTtl = $defaultTtl;
        $this->prefix = $prefix;

        $params = [
            'scheme' => 'tcp',
            'host'   => $config['host'] ?? '127.0.0.1',
            'port'   => (int) ($config['port'] ?? 6379),
        ];
        if (!empty($config['password'])) {
            $params['password'] = $config['password'];
        }
        if (isset($config['database'])) {
            $params['database'] = (int) $config['database'];
        }

        $this->client = new Client($params);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $raw = $this->client->get($this->prefix . $key);
        if ($raw === null) {
            return $default;
        }
        $value = @unserialize($raw);
        return $value === false && $raw !== serialize(false) ? $default : $value;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $payload = serialize($value);
        $fullKey = $this->prefix . $key;

        if ($ttl <= 0) {
            $this->client->set($fullKey, $payload);
        } else {
            $this->client->setex($fullKey, $ttl, $payload);
        }
        return true;
    }

    public function has(string $key): bool
    {
        return (bool) $this->client->exists($this->prefix . $key);
    }

    public function delete(string $key): bool
    {
        $this->client->del([$this->prefix . $key]);
        return true;
    }

    public function flush(): bool
    {
        $keys = $this->client->keys($this->prefix . '*');
        if (!empty($keys)) {
            $this->client->del($keys);
        }
        return true;
    }
}
