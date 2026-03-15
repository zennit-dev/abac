<?php

use Illuminate\Http\Request;
use zennit\ABAC\Contracts\CacheKeyStrategy;
use zennit\ABAC\Contracts\ContextEnricher;
use zennit\ABAC\Contracts\PolicyRepository;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Models\AbacPolicy;
use zennit\ABAC\Services\CacheKey\DefaultCacheKeyStrategy;
use zennit\ABAC\Tests\Fixtures\Models\Post;
use zennit\ABAC\Tests\Fixtures\Models\User;

function createActorAttributePolicy(string $tenantId): void
{
    $policy = AbacPolicy::query()->create([
        'resource' => Post::class,
        'method' => PolicyMethod::READ->value,
    ]);

    $chain = AbacChain::query()->create([
        'operator' => LogicalOperators::AND->value,
        'policy_id' => $policy->id,
    ]);

    AbacCheck::query()->create([
        'chain_id' => $chain->id,
        'operator' => ArithmeticOperators::EQUALS->value,
        'key' => 'actor.tenant_id',
        'value' => $tenantId,
    ]);
}

function createTitlePolicyFor(string $title): void
{
    $policy = AbacPolicy::query()->create([
        'resource' => Post::class,
        'method' => PolicyMethod::READ->value,
    ]);

    $chain = AbacChain::query()->create([
        'operator' => LogicalOperators::AND->value,
        'policy_id' => $policy->id,
    ]);

    AbacCheck::query()->create([
        'chain_id' => $chain->id,
        'operator' => ArithmeticOperators::EQUALS->value,
        'key' => 'resource.title',
        'value' => $title,
    ]);
}

it('applies custom context enricher bindings', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);

    app()->instance(ContextEnricher::class, new class implements ContextEnricher
    {
        public function enrich(AccessContext $context, Request $request): AccessContext
        {
            $context->environment['tenant_id'] = 'tenant-1';
            /** @var mixed $actor */
            $actor = $context->actor;
            $actor->tenant_id = 'tenant-1';

            return $context;
        }
    });

    createActorAttributePolicy('tenant-1');

    $user = User::query()->create([
        '_id' => 'u_enrich',
        'slug' => 'enrich-user',
        'name' => 'Enrich User',
        'role' => 'admin',
    ]);

    Post::query()->create([
        '_id' => 'p_enrich',
        'slug' => 'enrich-post',
        'title' => 'Enriched Post',
        'owner_id' => 'u_enrich',
    ]);

    $this->actingAs($user)->getJson('/posts/enrich-post')->assertOk();
});

it('applies custom policy repository bindings', function () {
    config()->set('abac.policy.default_policy_behavior', 'allow');
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);

    app()->instance(PolicyRepository::class, new class implements PolicyRepository
    {
        public function findByMethodAndResource(string $method, string $resource): ?AbacPolicy
        {
            return null;
        }
    });

    createTitlePolicyFor('Another Title');

    $user = User::query()->create([
        '_id' => 'u_policy',
        'slug' => 'policy-user',
        'name' => 'Policy User',
        'role' => 'member',
    ]);

    Post::query()->create([
        '_id' => 'p_policy',
        'slug' => 'policy-post',
        'title' => 'Original Title',
        'owner_id' => 'u_policy',
    ]);

    $this->actingAs($user)->getJson('/posts/policy-post')->assertOk();
});

it('uses bound cache key strategy implementation', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);

    $strategy = new class implements CacheKeyStrategy
    {
        public int $calls = 0;

        public function make(AccessContext $context, bool $includeContext): string
        {
            $this->calls++;

            return (new DefaultCacheKeyStrategy)->make($context, $includeContext);
        }
    };

    app()->instance(CacheKeyStrategy::class, $strategy);

    createTitlePolicyFor('Cache Hook Post');

    $user = User::query()->create([
        '_id' => 'u_cache_hook',
        'slug' => 'cache-hook-user',
        'name' => 'Cache Hook User',
        'role' => 'admin',
    ]);

    Post::query()->create([
        '_id' => 'p_cache_hook',
        'slug' => 'cache-hook-post',
        'title' => 'Cache Hook Post',
        'owner_id' => 'u_cache_hook',
    ]);

    $this->actingAs($user)->getJson('/posts/cache-hook-post')->assertOk();

    expect($strategy->calls)->toBeGreaterThan(0);
});
