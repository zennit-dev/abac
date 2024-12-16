<?php

namespace zennit\ABAC\Tests\Integration;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\DB;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\PermissionOperations;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Services\CacheService;
use zennit\ABAC\Tests\TestCase;
use zennit\ABAC\Tests\TestHelpers\PolicyBuilder;

class DatabaseIntegrationTest extends TestCase
{
    use PolicyBuilder;

    private AbacService $abacService;

    private CacheService $cacheService;

	/**
	 * @throws InvalidArgumentException
	 * @throws UnsupportedOperatorException
	 * @throws ValidationException
	 */
    public function test_handles_concurrent_policy_updates(): void
    {
        DB::beginTransaction();

        try {
            // Create initial policy
            $policy = $this->createPolicy('posts', PermissionOperations::UPDATE->value, [
                [
                    'operator' => PolicyOperators::EQUALS,
                    'attributes' => [['attribute_name' => 'role', 'attribute_value' => 'admin']],
                ],
            ]);

            // Simulate concurrent update
            DB::table('policies')
                ->where('id', $policy->id)
                ->update(['name' => 'Updated Policy']);

            // Try to evaluate policy
            $subject = new \stdClass();
            $subject->id = 1;

            UserAttribute::create([
                'subject_type' => get_class($subject),
                'subject_id' => $subject->id,
                'attribute_name' => 'role',
                'attribute_value' => 'admin',
            ]);

            $context = new AccessContext(
                subject: $subject,
                resource: 'posts',
                operation: PermissionOperations::UPDATE->value
            );

            $result = $this->abacService->evaluate($context);

            $this->assertTrue($result->granted);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function test_handles_cache_invalidation(): void
    {
        // Create and cache policy
        $policy = $this->createPolicy('posts', PermissionOperations::SHOW->value, [
            [
                'operator' => PolicyOperators::EQUALS,
                'attributes' => [['attribute_name' => 'status', 'attribute_value' => 'published']],
            ],
        ]);

        $cacheKey = 'policy:posts:index';
        $this->cacheService->remember($cacheKey, fn () => $policy);

        // Update policy
        $policy->update(['name' => 'Updated Policy']);
        $this->cacheService->forget($cacheKey);

        // Verify cache is invalidated
        $this->assertNull($this->cacheService->get($cacheKey));
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->abacService = $this->app->make(AbacService::class);
        $this->cacheService = $this->app->make(CacheService::class);
    }
}
