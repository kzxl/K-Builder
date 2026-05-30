<?php

declare(strict_types=1);

namespace KBuilder\Tests\Unit;

use KBuilder\Core\Cache\CacheManager;
use PHPUnit\Framework\TestCase;

class CacheManagerTest extends TestCase
{
    private string $cacheDir;
    private CacheManager $cache;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/kb_cache_test_' . uniqid();
        $this->cache = new CacheManager([
            'driver' => 'file',
            'prefix' => 'test_',
            'file'   => ['path' => $this->cacheDir, 'ttl' => 3600],
        ]);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->cacheDir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->cacheDir);
    }

    public function testSetAndGet(): void
    {
        $this->cache->set('foo', 'bar');
        $this->assertSame('bar', $this->cache->get('foo'));
    }

    public function testGetReturnsDefaultOnMiss(): void
    {
        $this->assertNull($this->cache->get('missing'));
        $this->assertSame('fallback', $this->cache->get('missing', 'fallback'));
    }

    public function testHasReflectsPresence(): void
    {
        $this->assertFalse($this->cache->has('x'));
        $this->cache->set('x', 123);
        $this->assertTrue($this->cache->has('x'));
    }

    public function testDeleteRemovesKey(): void
    {
        $this->cache->set('temp', 'v');
        $this->cache->delete('temp');
        $this->assertFalse($this->cache->has('temp'));
    }

    public function testStoresComplexValues(): void
    {
        $value = ['a' => 1, 'b' => [2, 3], 'c' => true];
        $this->cache->set('complex', $value);
        $this->assertSame($value, $this->cache->get('complex'));
    }

    public function testZeroTtlPersistsValue(): void
    {
        // ttl <= 0 nghĩa là không hết hạn (theo thiết kế FileStore)
        $this->cache->set('forever', 'kept', 0);
        $this->assertSame('kept', $this->cache->get('forever'));
    }

    public function testRememberComputesAndCaches(): void
    {
        $calls = 0;
        $producer = function () use (&$calls) {
            $calls++;
            return 'computed';
        };

        $first = $this->cache->remember('memo', $producer, 3600);
        $second = $this->cache->remember('memo', $producer, 3600);

        $this->assertSame('computed', $first);
        $this->assertSame('computed', $second);
        $this->assertSame(1, $calls, 'Callback chỉ được gọi một lần (lần sau lấy từ cache)');
    }

    public function testFlushClearsAll(): void
    {
        $this->cache->set('k1', 'v1');
        $this->cache->set('k2', 'v2');
        $this->cache->flush();
        $this->assertFalse($this->cache->has('k1'));
        $this->assertFalse($this->cache->has('k2'));
    }
}
