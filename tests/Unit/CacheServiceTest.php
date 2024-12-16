<?php

namespace zennit\ABAC\Tests\Unit;

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Services\CacheService;
use zennit\ABAC\Tests\TestCase;

class CacheServiceTest extends TestCase
{
    private CacheService $cache;

    /**
     * @throws InvalidArgumentException
     */
    public function test_remembers_values(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        $called = false;

        $result = $this->cache->remember($key, function () use ($value, &$called) {
            $called = true;

            return $value;
        });

        $this->assertTrue($called, 'Callback should be executed on first call');
        $this->assertEquals($value, $result);
        $this->assertEquals($value, $this->cache->get($key));

        // Test that callback isn't called again when value is cached
        $result2 = $this->cache->remember($key, function () use (&$called) {
            $called = false;

            return 'different value';
        });

        $this->assertEquals($value, $result2, 'Should return cached value');
        $this->assertTrue($called, 'Callback should not be executed when value is cached');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_forgets_values(): void
    {
        $key = 'test_key';
        $value = 'test_value';

        $this->cache->remember($key, fn () => $value);
        $this->assertEquals($value, $this->cache->get($key));

        $this->cache->forget($key);
        $this->assertNull($this->cache->get($key));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_flushes_cache(): void
    {
        $this->cache->remember('key1', fn () => 'value1');
        $this->cache->remember('key2', fn () => 'value2');

        $this->assertEquals('value1', $this->cache->get('key1'));
        $this->assertEquals('value2', $this->cache->get('key2'));

        $this->cache->flush();

        $this->assertNull($this->cache->get('key1'));
        $this->assertNull($this->cache->get('key2'));
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = $this->app->make(CacheService::class);
    }
}
