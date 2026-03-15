<?php

use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Facades\Abac;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Models\AbacPolicy;
use zennit\ABAC\Services\AbacCacheManager;
use zennit\ABAC\Tests\Fixtures\Models\Post;
use zennit\ABAC\Tests\Fixtures\Models\User;

function createTitlePolicy(string $value): void
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
        'value' => $value,
    ]);
}

it('allows access when actor attributes satisfy policy checks', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);

    createTitlePolicy('Post One');

    $user = User::query()->create([
        '_id' => 'u_admin',
        'slug' => 'admin',
        'name' => 'Admin',
        'role' => 'admin',
    ]);

    Post::query()->create([
        '_id' => 'p_1',
        'slug' => 'post-one',
        'title' => 'Post One',
        'owner_id' => 'u_admin',
    ]);

    $this->actingAs($user)->getJson('/posts/post-one')->assertOk();
});

it('allows access when any OR-linked grant matches', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);

    Abac::addPermission('read', Post::class, ['resource.title' => 'Allowed Title']);
    Abac::addPermission('read', Post::class, ['resource.title' => 'Other Title']);

    $user = User::query()->create([
        '_id' => 'u_or_grants',
        'slug' => 'or-grants-user',
        'name' => 'OR Grants User',
        'role' => 'member',
    ]);

    Post::query()->create([
        '_id' => 'p_or_grants',
        'slug' => 'or-grants-post',
        'title' => 'Allowed Title',
        'owner_id' => 'u_or_grants',
    ]);

    $this->actingAs($user)->getJson('/posts/or-grants-post')->assertOk();
});

it('denies show access when requested resource does not match any OR-linked grant', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);

    Abac::addPermission('read', Post::class, ['resource._id' => 'p_allowed_show']);
    Abac::addPermission('read', Post::class, ['resource._id' => 'p_other_allowed_show']);

    $user = User::query()->create([
        '_id' => 'u_show_scope',
        'slug' => 'show-scope-user',
        'name' => 'Show Scope User',
        'role' => 'member',
    ]);

    Post::query()->create([
        '_id' => 'p_allowed_show',
        'slug' => 'allowed-show-post',
        'title' => 'Allowed Show Post',
        'owner_id' => 'u_show_scope',
    ]);

    Post::query()->create([
        '_id' => 'p_other_allowed_show',
        'slug' => 'other-allowed-show-post',
        'title' => 'Other Allowed Show Post',
        'owner_id' => 'u_show_scope',
    ]);

    Post::query()->create([
        '_id' => 'p_denied_show',
        'slug' => 'denied-show-post',
        'title' => 'Denied Show Post',
        'owner_id' => 'u_show_scope',
    ]);

    $this->actingAs($user)->getJson('/posts/denied-show-post')->assertUnauthorized();
});

it('denies access when actor attributes do not satisfy policy checks', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);

    createTitlePolicy('Only for another title');

    $user = User::query()->create([
        '_id' => 'u_member',
        'slug' => 'member',
        'name' => 'Member',
        'role' => 'member',
    ]);

    Post::query()->create([
        '_id' => 'p_2',
        'slug' => 'post-two',
        'title' => 'Post Two',
        'owner_id' => 'u_member',
    ]);

    $this->actingAs($user)->getJson('/posts/post-two')->assertUnauthorized();
});

it('supports route model binding with custom primary keys and route keys', function () {
    config()->set('abac.database.primary_key', 'id');
    config()->set('abac.database.fallback_primary_key', '_id');
    config()->set('abac.middleware.resource_patterns', [
        'users/([^/]+)/posts/([^/]+)' => Post::class,
    ]);

    createTitlePolicy('Bound Post');

    $user = User::query()->create([
        '_id' => 'u_bind',
        'slug' => 'bound-user',
        'name' => 'Bound User',
        'role' => 'admin',
    ]);

    Post::query()->create([
        '_id' => 'p_bound',
        'slug' => 'bound-post',
        'title' => 'Bound Post',
        'owner_id' => 'u_bind',
    ]);

    $this->actingAs($user)->getJson('/users/bound-user/posts/bound-post')->assertOk();
});

it('uses fallback key for path-pattern resolution when primary key differs', function () {
    config()->set('abac.database.primary_key', 'id');
    config()->set('abac.database.fallback_primary_key', '_id');
    config()->set('abac.middleware.resource_patterns', [
        'manual-posts/([^/]+)' => Post::class,
    ]);

    createTitlePolicy('Manual Post');

    $user = User::query()->create([
        '_id' => 'u_manual',
        'slug' => 'manual-user',
        'name' => 'Manual User',
        'role' => 'admin',
    ]);

    Post::query()->create([
        '_id' => 'p_manual',
        'slug' => 'manual-post',
        'title' => 'Manual Post',
        'owner_id' => 'u_manual',
    ]);

    $this->actingAs($user)->getJson('/manual-posts/p_manual')->assertOk();
});

it('applies explicit policy fallback behavior when no policy exists', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);
    config()->set('abac.cache.enabled', false);

    $user = User::query()->create([
        '_id' => 'u_fallback',
        'slug' => 'fallback-user',
        'name' => 'Fallback User',
        'role' => 'member',
    ]);

    Post::query()->create([
        '_id' => 'p_fallback',
        'slug' => 'fallback-post',
        'title' => 'Fallback Post',
        'owner_id' => 'u_fallback',
    ]);

    config()->set('abac.policy.default_policy_behavior', 'allow');
    $this->actingAs($user)->getJson('/posts/fallback-post')->assertOk();

    config()->set('abac.policy.default_policy_behavior', 'deny');
    $this->actingAs($user)->getJson('/posts/fallback-post')->assertUnauthorized();
});

it('denies access by default when no policy exists', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);
    config()->set('abac.cache.enabled', false);

    $user = User::query()->create([
        '_id' => 'u_default_deny',
        'slug' => 'default-deny-user',
        'name' => 'Default Deny User',
        'role' => 'member',
    ]);

    Post::query()->create([
        '_id' => 'p_default_deny',
        'slug' => 'default-deny-post',
        'title' => 'Default Deny Post',
        'owner_id' => 'u_default_deny',
    ]);

    $this->actingAs($user)->getJson('/posts/default-deny-post')->assertUnauthorized();
});

it('allows empty collection reads when policy is valid but no rows match', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts' => Post::class,
    ]);

    createTitlePolicy('No Matching Posts');

    $user = User::query()->create([
        '_id' => 'u_collection',
        'slug' => 'collection-user',
        'name' => 'Collection User',
        'role' => 'member',
    ]);

    $this->actingAs($user)
        ->getJson('/posts')
        ->assertOk()
        ->assertJson([
            'ok' => true,
            'count' => 0,
        ]);
});

it('does not return unauthorized when middleware throws internal errors', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);
    config()->set('abac.middleware.actor_method', 'notARealMethod');

    $user = User::query()->create([
        '_id' => 'u_error',
        'slug' => 'error-user',
        'name' => 'Error User',
        'role' => 'admin',
    ]);

    Post::query()->create([
        '_id' => 'p_error',
        'slug' => 'error-post',
        'title' => 'Error Post',
        'owner_id' => 'u_error',
    ]);

    $this->actingAs($user)->getJson('/posts/error-post')->assertStatus(500);
});

it('invalidates memoized decisions when checks are updated', function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);

    createTitlePolicy('Cache Post');

    $user = User::query()->create([
        '_id' => 'u_cache',
        'slug' => 'cache-user',
        'name' => 'Cache User',
        'role' => 'admin',
    ]);

    Post::query()->create([
        '_id' => 'p_cache',
        'slug' => 'cache-post',
        'title' => 'Cache Post',
        'owner_id' => 'u_cache',
    ]);

    $this->actingAs($user)->getJson('/posts/cache-post')->assertOk();

    AbacCheck::query()->firstOrFail()->update(['value' => 'Not Cache Post']);

    $this->actingAs($user)->getJson('/posts/cache-post')->assertUnauthorized();
});

it('can skip cache flush hooks during writes when configured', function () {
    config()->set('abac.cache.flush_on_write', false);
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);

    createTitlePolicy('Bulk Post');

    $user = User::query()->create([
        '_id' => 'u_bulk',
        'slug' => 'bulk-user',
        'name' => 'Bulk User',
        'role' => 'admin',
    ]);

    Post::query()->create([
        '_id' => 'p_bulk',
        'slug' => 'bulk-post',
        'title' => 'Bulk Post',
        'owner_id' => 'u_bulk',
    ]);

    $this->actingAs($user)->getJson('/posts/bulk-post')->assertOk();

    AbacCheck::query()->firstOrFail()->update(['value' => 'Not Bulk Post']);

    $this->actingAs($user)->getJson('/posts/bulk-post')->assertOk();

    app(AbacCacheManager::class)->flush();

    $this->actingAs($user)->getJson('/posts/bulk-post')->assertUnauthorized();
});
