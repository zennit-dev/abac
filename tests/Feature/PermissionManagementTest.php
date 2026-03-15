<?php

use zennit\ABAC\Facades\Abac;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Models\AbacPolicy;
use zennit\ABAC\Tests\Fixtures\Models\Post;

beforeEach(function () {
    config()->set('abac.middleware.resource_patterns', [
        'posts/([^/]+)' => Post::class,
    ]);

    AbacPolicy::query()->delete();
    AbacChain::query()->delete();
    AbacCheck::query()->delete();
});

describe('addPermission', function () {
    it('creates policy and root chain on first permission for method/resource', function () {
        Abac::addPermission('read', Post::class, ['role' => 'admin']);

        $policy = AbacPolicy::query()->where('method', 'read')->where('resource', Post::class)->first();
        $rootChain = AbacChain::query()->where('policy_id', $policy?->id)->first();

        expect($policy)->not->toBeNull()
            ->and($rootChain)->not->toBeNull()
            ->and($rootChain->operator)->toBe('or')
            ->and($rootChain->chain_id)->toBeNull();
    });

    it('defaults shorthand keys to actor prefix', function () {
        $grant = Abac::addPermission('read', Post::class, [
            'user_id' => 123,
            'role' => 'admin',
        ]);

        expect($grant->constraints->pluck('key')->all())->toEqualCanonicalizing([
            'actor.role',
            'actor.user_id',
        ]);
    });

    it('accepts explicit actor and resource keys', function () {
        $grant = Abac::addPermission('read', Post::class, [
            'actor.user_id' => 123,
            'resource.owner_id' => 456,
        ]);

        expect($grant->constraints->pluck('key')->all())->toEqualCanonicalizing([
            'actor.user_id',
            'resource.owner_id',
        ]);
    });

    it('accepts explicit constraint array format with operators', function () {
        $grant = Abac::addPermission('read', Post::class, [
            ['key' => 'actor.role', 'operator' => 'equals', 'value' => 'admin'],
            ['key' => 'resource.status', 'operator' => 'not_equals', 'value' => 'draft'],
        ]);

        expect($grant->constraints->count())->toBe(2)
            ->and($grant->constraints->pluck('operator')->all())->toEqualCanonicalizing(['equals', 'not_equals']);
    });

    it('accepts DSL string format', function () {
        $grant = Abac::addPermission('read', Post::class, 'actor.role=admin and resource.status=draft');

        expect($grant->constraints->count())->toBe(2)
            ->and($grant->constraints->pluck('key')->all())->toEqualCanonicalizing(['actor.role', 'resource.status'])
            ->and($grant->constraints->pluck('value')->all())->toEqualCanonicalizing(['admin', 'draft']);
    });

    it('supports DSL operators = != > < >= <=', function () {
        $grant = Abac::addPermission('read', Post::class, 'actor.score>=10 and resource.priority<5');

        expect($grant->constraints->pluck('operator')->all())->toEqualCanonicalizing(['greater_than_equals', 'less_than']);
    });

    it('supports DSL string operators ~ !~ ^= !^ $= !$', function () {
        $grant = Abac::addPermission('read', Post::class, 'actor.name~bob and resource.title^=Weekly and actor.code$=XY');

        expect($grant->constraints->pluck('operator')->all())->toEqualCanonicalizing([
            'contains',
            'starts_with',
            'ends_with',
        ]);
    });

    it('resolves string resource alias to model class', function () {
        $grant = Abac::addPermission('read', 'posts', ['role' => 'admin']);

        expect($grant->resource)->toBe(Post::class);
    });

    it('throws on invalid method', function () {
        expect(fn () => Abac::addPermission('invalid', Post::class, ['role' => 'admin']))
            ->toThrow(InvalidArgumentException::class, "Unsupported method 'invalid'");
    });

    it('throws on empty constraints', function () {
        expect(fn () => Abac::addPermission('read', Post::class, []))
            ->toThrow(InvalidArgumentException::class, 'Permission constraints cannot be empty');
    });

    it('throws on invalid constraint key prefix', function () {
        expect(fn () => Abac::addPermission('read', Post::class, ['invalid.key' => 'value']))
            ->toThrow(InvalidArgumentException::class, "Constraint key 'invalid.key' must start with actor., resource., or environment");
    });

    it('throws on invalid operator', function () {
        expect(fn () => Abac::addPermission('read', Post::class, [
            ['key' => 'actor.role', 'operator' => 'invalid_op', 'value' => 'admin'],
        ]))->toThrow(InvalidArgumentException::class, "Unsupported operator 'invalid_op'");
    });
});

describe('getPermissions', function () {
    beforeEach(function () {
        Abac::addPermission('read', Post::class, ['role' => 'admin']);
        Abac::addPermission('read', Post::class, ['role' => 'editor']);
        Abac::addPermission('update', Post::class, ['role' => 'admin']);
    });

    it('returns all grants when no filters applied', function () {
        $grants = Abac::getPermissions();

        expect($grants->count())->toBe(3);
    });

    it('filters by method', function () {
        $grants = Abac::getPermissions('read');

        expect($grants->count())->toBe(2)
            ->and($grants->pluck('method')->unique()->all())->toBe(['read']);
    });

    it('filters by resource', function () {
        $grants = Abac::getPermissions(null, Post::class);

        expect($grants->count())->toBe(3);
    });

    it('filters by method and resource', function () {
        $grants = Abac::getPermissions('update', Post::class);

        expect($grants->count())->toBe(1)
            ->and($grants->first()?->method)->toBe('update');
    });

    it('filters by constraint_key', function () {
        $grants = Abac::getPermissions(null, null, ['constraint_key' => 'actor.role']);

        expect($grants->count())->toBe(3);
    });
});

describe('getPermission', function () {
    it('returns grant by id', function () {
        $created = Abac::addPermission('read', Post::class, ['role' => 'admin']);
        $fetched = Abac::getPermission($created->id);

        expect($fetched)->not->toBeNull()
            ->and($fetched?->id)->toBe($created->id)
            ->and($fetched?->method)->toBe('read');
    });

    it('returns null for non-existent grant', function () {
        $fetched = Abac::getPermission(99999);

        expect($fetched)->toBeNull();
    });

    it('returns null for root chain (not a grant)', function () {
        Abac::addPermission('read', Post::class, ['role' => 'admin']);
        $policy = AbacPolicy::query()->first();
        $rootChain = AbacChain::query()->where('policy_id', $policy->id)->first();

        $fetched = Abac::getPermission($rootChain->id);

        expect($fetched)->toBeNull();
    });
});

describe('updatePermission', function () {
    it('replaces constraints on existing grant', function () {
        $grant = Abac::addPermission('read', Post::class, ['role' => 'admin']);
        $originalConstraints = $grant->constraints->count();

        $updated = Abac::updatePermission($grant->id, ['role' => 'superadmin', 'level' => 5]);

        expect($updated->constraints->count())->toBe(2)
            ->and($updated->constraints->pluck('value')->all())->toContain('superadmin', '5')
            ->and($originalConstraints)->toBe(1);
    });

    it('throws on non-existent grant id', function () {
        expect(fn () => Abac::updatePermission(99999, ['role' => 'admin']))
            ->toThrow(InvalidArgumentException::class, 'Permission grant 99999 does not exist');
    });

    it('throws on root chain id', function () {
        Abac::addPermission('read', Post::class, ['role' => 'admin']);
        $policy = AbacPolicy::query()->first();
        $rootChain = AbacChain::query()->where('policy_id', $policy->id)->first();

        expect(fn () => Abac::updatePermission($rootChain->id, ['role' => 'admin']))
            ->toThrow(InvalidArgumentException::class, "Permission grant $rootChain->id does not exist");
    });
});

describe('removePermission', function () {
    it('deletes single grant by id', function () {
        $grant = Abac::addPermission('read', Post::class, ['role' => 'admin']);
        $grantId = $grant->id;

        $result = Abac::removePermission($grantId);

        expect($result)->toBeTrue()
            ->and(Abac::getPermission($grantId))->toBeNull();
    });

    it('returns false for non-existent grant', function () {
        $result = Abac::removePermission(99999);

        expect($result)->toBeFalse();
    });

    it('returns false for root chain', function () {
        Abac::addPermission('read', Post::class, ['role' => 'admin']);
        $policy = AbacPolicy::query()->first();
        $rootChain = AbacChain::query()->where('policy_id', $policy->id)->first();

        $result = Abac::removePermission($rootChain->id);

        expect($result)->toBeFalse();
    });
});

describe('removePermissions', function () {
    beforeEach(function () {
        Abac::addPermission('read', Post::class, ['role' => 'admin']);
        Abac::addPermission('read', Post::class, ['role' => 'editor']);
        Abac::addPermission('update', Post::class, ['role' => 'admin']);
    });

    it('removes all grants for method/resource combination', function () {
        $removed = Abac::removePermissions('read', Post::class);

        expect($removed)->toBe(2)
            ->and(Abac::getPermissions('read', Post::class)->count())->toBe(0)
            ->and(Abac::getPermissions('update', Post::class)->count())->toBe(1);
    });

    it('removes only matching constraints when specified', function () {
        $removed = Abac::removePermissions('read', Post::class, ['role' => 'admin']);

        expect($removed)->toBe(1)
            ->and(Abac::getPermissions('read', Post::class)->count())->toBe(1)
            ->and(Abac::getPermissions('read', Post::class)->first()?->constraints->pluck('value')->first())->toBe('editor');
    });

    it('returns 0 when no matching policy exists', function () {
        $removed = Abac::removePermissions('delete', Post::class);

        expect($removed)->toBe(0);
    });

    it('returns 0 when constraints do not match', function () {
        $removed = Abac::removePermissions('read', Post::class, ['role' => 'superadmin']);

        expect($removed)->toBe(0)
            ->and(Abac::getPermissions('read', Post::class)->count())->toBe(2);
    });
});

describe('widening behavior', function () {
    it('creates OR branches for each unique grant', function () {
        Abac::addPermission('read', Post::class, ['role' => 'admin']);
        Abac::addPermission('read', Post::class, ['role' => 'editor']);

        $policy = AbacPolicy::query()->where('method', 'read')->where('resource', Post::class)->first();
        $rootChain = AbacChain::query()->where('policy_id', $policy->id)->first();
        $branches = AbacChain::query()->where('chain_id', $rootChain->id)->get();

        expect($rootChain->operator)->toBe('or')
            ->and($branches->count())->toBe(2);
    });

    it('reuses existing grant for identical constraints (idempotent)', function () {
        $first = Abac::addPermission('read', Post::class, ['role' => 'admin']);
        $second = Abac::addPermission('read', Post::class, ['role' => 'admin']);

        expect($second->id)->toBe($first->id);

        $policy = AbacPolicy::query()->where('method', 'read')->where('resource', Post::class)->first();
        $rootChain = AbacChain::query()->where('policy_id', $policy->id)->first();
        $branches = AbacChain::query()->where('chain_id', $rootChain->id)->count();

        expect($branches)->toBe(1);
    });

    it('normalizes constraint order for idempotency', function () {
        $first = Abac::addPermission('read', Post::class, ['role' => 'admin', 'level' => 5]);
        $second = Abac::addPermission('read', Post::class, ['level' => 5, 'role' => 'admin']);

        expect($second->id)->toBe($first->id);
    });
});

describe('DSL parsing', function () {
    it('handles empty DSL gracefully', function () {
        expect(fn () => Abac::addPermission('read', Post::class, ''))
            ->toThrow(InvalidArgumentException::class, 'Constraint DSL cannot be empty');
    });

    it('throws on malformed DSL expression', function () {
        expect(fn () => Abac::addPermission('read', Post::class, 'invalid expression'))
            ->toThrow(InvalidArgumentException::class, "Invalid constraint expression 'invalid expression'");
    });

    it('strips quotes from DSL values', function () {
        $grant = Abac::addPermission('read', Post::class, 'actor.name="test"');

        expect($grant->constraints->first()?->value)->toBe('test');
    });

    it('throws on unsupported DSL operator', function () {
        expect(fn () => Abac::addPermission('read', Post::class, 'actor.role ?? admin'))
            ->toThrow(InvalidArgumentException::class, 'Invalid constraint expression');
    });
});

describe('edge cases', function () {
    it('handles duplicate policy creation gracefully', function () {
        Abac::addPermission('read', Post::class, ['role' => 'admin']);
        $policyCountBefore = AbacPolicy::query()->count();

        Abac::addPermission('read', Post::class, ['role' => 'editor']);

        expect(AbacPolicy::query()->count())->toBe($policyCountBefore);
    });

    it('preserves root chain operator on subsequent adds', function () {
        Abac::addPermission('read', Post::class, ['role' => 'admin']);
        Abac::addPermission('read', Post::class, ['role' => 'editor']);

        $policy = AbacPolicy::query()->where('method', 'read')->first();
        $rootChain = AbacChain::query()->where('policy_id', $policy->id)->first();

        expect($rootChain->operator)->toBe('or');
    });

    it('loads constraints with grant', function () {
        $grant = Abac::addPermission('read', Post::class, [
            'role' => 'admin',
            'resource.owner_id' => 123,
        ]);

        $fetched = Abac::getPermission($grant->id);

        expect($fetched?->constraints->count())->toBe(2)
            ->and($fetched?->constraints->pluck('key')->all())->toContain('actor.role', 'resource.owner_id');
    });
});
