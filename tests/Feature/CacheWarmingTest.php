<?php

namespace zennit\ABAC\Tests\Feature;

use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Services\CacheService;
use zennit\ABAC\Services\PolicyCacheWarmer;
use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Tests\TestHelpers\PolicyBuilder;

class CacheWarmingTest extends TestCase
{
    use PolicyBuilder;

    private PolicyCacheWarmer $cacheWarmer;

    private CacheService $cache;

    /**
     * @throws InvalidArgumentException
     */
    public function test_warms_cache_for_all_policies(): void
    {
        // Create test policies
        $this->createPolicy('posts', PermissionOperations::INDEX->value, [
            [
                'operator' => PolicyOperators::EQUALS,
                'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'user']],
            ],
        ]);

        $this->createPolicy('posts', PermissionOperations::UPDATE->value, [
            [
                'operator' => PolicyOperators::EQUALS,
                'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'admin']],
            ],
        ]);

        // Warm cache
        $this->cacheWarmer->warmAll();

        // Verify cache
        $this->assertNotNull($this->cache->get('policy:posts:index'));
        $this->assertNotNull($this->cache->get('policy:posts:update'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_warms_cache_for_specific_resource(): void
    {
        // Create policies for different resources
        $this->createPolicy('posts', PermissionOperations::INDEX->value, [
            [
                'operator' => PolicyOperators::EQUALS,
                'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'user']],
            ],
        ]);

        $this->createPolicy('comments', PermissionOperations::UPDATE->value, [
            [
                'operator' => PolicyOperators::EQUALS,
                'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'admin']],
            ],
        ]);

        // Warm cache
        $this->cacheWarmer->warmAll();

        // Verify cache
        $this->assertNotNull($this->cache->get('policy:posts:index'));
        $this->assertNotNull($this->cache->get('policy:comments:update'));
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheWarmer = $this->app->make(PolicyCacheWarmer::class);
        $this->cache = $this->app->make(CacheService::class);
    }
}
