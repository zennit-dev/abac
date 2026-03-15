<?php

namespace zennit\ABAC\Services\Permissions;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;
use zennit\ABAC\DTO\PermissionConstraint;
use zennit\ABAC\DTO\PermissionGrant;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Enums\Operators\StringOperators;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Models\AbacPolicy;

class PermissionManager
{
    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>|string  $constraints
     *
     * @throws Throwable
     */
    public function addPermission(string $method, string $resource, array|string $constraints): PermissionGrant
    {
        $normalizedMethod = $this->normalizeMethod($method);
        $normalizedResource = $this->resolveResource($resource);
        $normalizedConstraints = $this->normalizeConstraints($constraints);

        return DB::transaction(function () use ($normalizedMethod, $normalizedResource, $normalizedConstraints): PermissionGrant {
            $policy = AbacPolicy::query()->firstOrCreate([
                'method' => $normalizedMethod,
                'resource' => $normalizedResource,
            ]);

            $rootChain = $this->findOrCreateRootChain($policy);

            $existingGrant = $this->findMatchingGrant($rootChain->id, $normalizedConstraints);
            if (! is_null($existingGrant)) {
                return $this->toGrant($existingGrant, $policy);
            }

            $grantChain = AbacChain::query()->create([
                'operator' => LogicalOperators::AND->value,
                'chain_id' => $rootChain->id,
            ]);

            $this->createChecks($grantChain->id, $normalizedConstraints);

            return $this->toGrant($grantChain, $policy);
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, PermissionGrant>
     */
    public function getPermissions(?string $method = null, ?string $resource = null, array $filters = []): Collection
    {
        $policies = AbacPolicy::query()
            ->when(! is_null($method), fn ($query) => $query->where('method', $this->normalizeMethod($method)))
            ->when(! is_null($resource), fn ($query) => $query->where('resource', $this->resolveResource($resource)))
            ->get();

        $grants = collect();

        foreach ($policies as $policy) {
            $rootChain = AbacChain::query()->where('policy_id', $policy->id)->first();
            if (is_null($rootChain)) {
                continue;
            }

            $policyGrants = AbacChain::query()->where('chain_id', $rootChain->id)->get();
            foreach ($policyGrants as $grantChain) {
                $grants->push($this->toGrant($grantChain, $policy));
            }
        }

        if (isset($filters['constraint_key'])) {
            $constraintKey = (string) $filters['constraint_key'];
            $grants = $grants->filter(function (PermissionGrant $grant) use ($constraintKey): bool {
                return $grant->constraints->contains(
                    fn (PermissionConstraint $constraint): bool => $constraint->key === $constraintKey
                );
            })->values();
        }

        return $grants;
    }

    public function getPermission(int $grantId): ?PermissionGrant
    {
        $managedGrant = $this->resolveManagedGrant($grantId);
        if (is_null($managedGrant)) {
            return null;
        }

        return $this->toGrant($managedGrant['grant'], $managedGrant['policy']);
    }

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>|string  $constraints
     *
     * @throws Throwable
     */
    public function updatePermission(int $grantId, array|string $constraints): PermissionGrant
    {
        $normalizedConstraints = $this->normalizeConstraints($constraints);

        return DB::transaction(function () use ($grantId, $normalizedConstraints): PermissionGrant {
            $managedGrant = $this->resolveManagedGrantOrFail($grantId);
            $grantChain = $managedGrant['grant'];
            $policy = $managedGrant['policy'];

            AbacCheck::query()->where('chain_id', $grantChain->id)->delete();
            $this->createChecks($grantChain->id, $normalizedConstraints);

            $freshGrant = AbacChain::query()->whereKey($grantChain->id)->first() ?? $grantChain;

            return $this->toGrant($freshGrant, $policy);
        });
    }

    public function removePermission(int $grantId): bool
    {
        $managedGrant = $this->resolveManagedGrant($grantId);
        if (is_null($managedGrant)) {
            return false;
        }

        return (bool) $managedGrant['grant']->delete();
    }

    /**
     * @return array{grant: AbacChain, policy: AbacPolicy}|null
     */
    private function resolveManagedGrant(int $grantId): ?array
    {
        $grantChain = AbacChain::query()->whereKey($grantId)->first();
        $parentChainId = is_null($grantChain) ? null : $grantChain->getAttribute('chain_id');
        if (is_null($grantChain) || is_null($parentChainId)) {
            return null;
        }

        $rootChain = AbacChain::query()->whereKey((int) $parentChainId)->first();
        $policyId = is_null($rootChain) ? null : $rootChain->getAttribute('policy_id');
        if (is_null($rootChain) || is_null($policyId)) {
            return null;
        }

        $policy = AbacPolicy::query()->whereKey((int) $policyId)->first();
        if (is_null($policy)) {
            return null;
        }

        return ['grant' => $grantChain, 'policy' => $policy];
    }

    /**
     * @return array{grant: AbacChain, policy: AbacPolicy}
     */
    private function resolveManagedGrantOrFail(int $grantId): array
    {
        $managedGrant = $this->resolveManagedGrant($grantId);
        if (is_null($managedGrant)) {
            throw new InvalidArgumentException("Permission grant $grantId does not exist.");
        }

        return $managedGrant;
    }

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>|string|null  $constraints
     */
    public function removePermissions(string $method, string $resource, array|string|null $constraints = null): int
    {
        $normalizedMethod = $this->normalizeMethod($method);
        $normalizedResource = $this->resolveResource($resource);
        $normalizedConstraints = is_null($constraints) ? null : $this->normalizeConstraints($constraints);

        $policy = AbacPolicy::query()
            ->where('method', $normalizedMethod)
            ->where('resource', $normalizedResource)
            ->first();

        if (is_null($policy)) {
            return 0;
        }

        $rootChain = AbacChain::query()->where('policy_id', $policy->id)->first();
        if (is_null($rootChain)) {
            return 0;
        }

        $grants = AbacChain::query()->where('chain_id', $rootChain->id)->get();
        $deleted = 0;

        foreach ($grants as $grant) {
            if (! is_null($normalizedConstraints)) {
                $grantConstraints = $this->getNormalizedConstraintsForChain($grant->id);
                if ($grantConstraints !== $normalizedConstraints) {
                    continue;
                }
            }

            if ($grant->delete()) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function findOrCreateRootChain(AbacPolicy $policy): AbacChain
    {
        $rootChain = AbacChain::query()->where('policy_id', $policy->id)->first();

        if (! is_null($rootChain)) {
            if ($rootChain->getAttribute('operator') !== LogicalOperators::OR->value) {
                $rootChain->fill(['operator' => LogicalOperators::OR->value])->save();
            }

            return $rootChain;
        }

        return AbacChain::query()->create([
            'operator' => LogicalOperators::OR->value,
            'policy_id' => $policy->id,
        ]);
    }

    /**
     * @param  array<int, array{key: string, operator: string, value: string}>  $constraints
     */
    private function findMatchingGrant(int $rootChainId, array $constraints): ?AbacChain
    {
        $grants = AbacChain::query()->where('chain_id', $rootChainId)->get();

        foreach ($grants as $grant) {
            if ($this->getNormalizedConstraintsForChain($grant->id) === $constraints) {
                return $grant;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{key: string, operator: string, value: string}>  $constraints
     */
    private function createChecks(int $chainId, array $constraints): void
    {
        foreach ($constraints as $constraint) {
            AbacCheck::query()->create([
                'chain_id' => $chainId,
                'operator' => $constraint['operator'],
                'key' => $constraint['key'],
                'value' => $constraint['value'],
            ]);
        }
    }

    private function toGrant(AbacChain $grantChain, AbacPolicy $policy): PermissionGrant
    {
        $checks = AbacCheck::query()
            ->where('chain_id', $grantChain->id)
            ->orderBy('id')
            ->get();

        $constraints = $checks->map(
            fn (AbacCheck $check): PermissionConstraint => new PermissionConstraint(
                (string) $check->getAttribute('key'),
                (string) $check->getAttribute('operator'),
                (string) $check->getAttribute('value')
            )
        );

        return new PermissionGrant(
            id: $grantChain->id,
            method: (string) $policy->getAttribute('method'),
            resource: (string) $policy->getAttribute('resource'),
            constraints: $constraints,
        );
    }

    /**
     * @return array<int, array{key: string, operator: string, value: string}>
     */
    private function getNormalizedConstraintsForChain(int $chainId): array
    {
        $constraints = AbacCheck::query()
            ->where('chain_id', $chainId)
            ->get()
            ->map(function (AbacCheck $check): array {
                return [
                    'key' => (string) $check->getAttribute('key'),
                    'operator' => (string) $check->getAttribute('operator'),
                    'value' => (string) $check->getAttribute('value'),
                ];
            })
            ->all();

        return $this->sortConstraints($constraints);
    }

    private function normalizeMethod(string $method): string
    {
        $normalized = strtolower(trim($method));
        if (! PolicyMethod::isValid($normalized)) {
            throw new InvalidArgumentException("Unsupported method '$method'.");
        }

        return $normalized;
    }

    private function resolveResource(string $resource): string
    {
        $resource = trim($resource);
        if ($resource === '') {
            throw new InvalidArgumentException('Resource cannot be empty.');
        }

        if (class_exists($resource)) {
            return $resource;
        }

        $normalized = strtolower($resource);
        $configured = config('abac.permissions.resources', []);
        if (isset($configured[$normalized]) && is_string($configured[$normalized])) {
            return $configured[$normalized];
        }

        $patterns = config('abac.middleware.resource_patterns', []);
        $resources = array_values(array_unique(array_values($patterns)));

        foreach ($resources as $resourceClass) {
            if (! is_string($resourceClass)) {
                continue;
            }

            $alias = Str::plural(Str::snake(class_basename($resourceClass)));
            if ($alias === $normalized || class_basename($resourceClass) === $resource) {
                return $resourceClass;
            }
        }

        throw new InvalidArgumentException("Resource '$resource' could not be resolved to a model class.");
    }

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>|string  $constraints
     * @return array<int, array{key: string, operator: string, value: string}>
     */
    private function normalizeConstraints(array|string $constraints): array
    {
        if (is_string($constraints)) {
            $constraints = $this->parseConstraintDsl($constraints);
        }

        if ($constraints === []) {
            throw new InvalidArgumentException('Permission constraints cannot be empty.');
        }

        $first = Arr::first($constraints);
        if (is_array($first) && array_is_list($constraints)) {
            $normalized = collect($constraints)
                ->map(function (array $constraint): array {
                    $key = $this->normalizeConstraintKey((string) ($constraint['key'] ?? ''));
                    $operator = $this->normalizeOperator((string) ($constraint['operator'] ?? ArithmeticOperators::EQUALS->value));
                    $value = array_key_exists('value', $constraint)
                        ? (string) $constraint['value']
                        : '';

                    if ($value === '') {
                        throw new InvalidArgumentException("Constraint '$key' requires a non-empty value.");
                    }

                    return [
                        'key' => $key,
                        'operator' => $operator,
                        'value' => $value,
                    ];
                })
                ->all();

            return $this->sortConstraints($normalized);
        }

        $normalized = collect($constraints)
            ->map(function (mixed $value, mixed $key): array {
                if (! is_string($key) || trim($key) === '') {
                    throw new InvalidArgumentException('Constraint keys must be non-empty strings.');
                }

                $normalizedKey = $this->normalizeConstraintKey($key);

                if (is_array($value)) {
                    $operator = $this->normalizeOperator((string) ($value['operator'] ?? ArithmeticOperators::EQUALS->value));
                    $constraintValue = array_key_exists('value', $value) ? (string) $value['value'] : '';
                } else {
                    $operator = ArithmeticOperators::EQUALS->value;
                    $constraintValue = (string) $value;
                }

                if ($constraintValue === '') {
                    throw new InvalidArgumentException("Constraint '$normalizedKey' requires a non-empty value.");
                }

                return [
                    'key' => $normalizedKey,
                    'operator' => $operator,
                    'value' => $constraintValue,
                ];
            })
            ->values()
            ->all();

        return $this->sortConstraints($normalized);
    }

    private function normalizeConstraintKey(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            throw new InvalidArgumentException('Constraint key cannot be empty.');
        }

        if (! str_contains($key, '.')) {
            return 'actor.'.$key;
        }

        [$prefix] = explode('.', $key, 2);
        $prefix = strtolower($prefix);

        if (! in_array($prefix, ['actor', 'resource', 'environment'], true)) {
            throw new InvalidArgumentException("Constraint key '$key' must start with actor., resource., or environment.");
        }

        return $prefix.'.'.explode('.', $key, 2)[1];
    }

    private function normalizeOperator(string $operator): string
    {
        $operator = strtolower(trim($operator));

        $supportedOperators = array_merge(
            ArithmeticOperators::values(),
            StringOperators::values()
        );

        if (! in_array($operator, $supportedOperators, true)) {
            throw new InvalidArgumentException("Unsupported operator '$operator'.");
        }

        return $operator;
    }

    /**
     * @return array<int, array{key: string, operator: string, value: string}>
     */
    private function parseConstraintDsl(string $dsl): array
    {
        $dsl = trim($dsl);
        if ($dsl === '') {
            throw new InvalidArgumentException('Constraint DSL cannot be empty.');
        }

        $segments = preg_split('/\s+and\s+/i', $dsl);
        if ($segments === false) {
            throw new InvalidArgumentException('Invalid constraint DSL.');
        }

        $normalized = [];
        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '') {
                continue;
            }

            if (! preg_match('/^(?<key>[a-zA-Z0-9_.]+)\s*(?<op>!=|>=|<=|=|>|<|!~|~|!\^|\^=|!\$|\$=)\s*(?<value>.+)$/', $segment, $matches)) {
                throw new InvalidArgumentException("Invalid constraint expression '$segment'.");
            }

            $key = $this->normalizeConstraintKey($matches['key']);
            $operator = $this->normalizeDslOperator($matches['op']);
            $value = trim($matches['value'], " \t\n\r\0\x0B\"'");

            if ($value === '') {
                throw new InvalidArgumentException("Constraint '$key' requires a non-empty value.");
            }

            $normalized[] = [
                'key' => $key,
                'operator' => $operator,
                'value' => $value,
            ];
        }

        if ($normalized === []) {
            throw new InvalidArgumentException('Constraint DSL produced no constraints.');
        }

        return $this->sortConstraints($normalized);
    }

    private function normalizeDslOperator(string $operator): string
    {
        return match ($operator) {
            '=' => ArithmeticOperators::EQUALS->value,
            '!=' => ArithmeticOperators::NOT_EQUALS->value,
            '>' => ArithmeticOperators::GREATER_THAN->value,
            '<' => ArithmeticOperators::LESS_THAN->value,
            '>=' => ArithmeticOperators::GREATER_THAN_EQUALS->value,
            '<=' => ArithmeticOperators::LESS_THAN_EQUALS->value,
            '~' => StringOperators::CONTAINS->value,
            '!~' => StringOperators::NOT_CONTAINS->value,
            '^=' => StringOperators::STARTS_WITH->value,
            '!^' => StringOperators::NOT_STARTS_WITH->value,
            '$=' => StringOperators::ENDS_WITH->value,
            '!$' => StringOperators::NOT_ENDS_WITH->value,
            default => throw new InvalidArgumentException("Unsupported DSL operator '$operator'."),
        };
    }

    /**
     * @param  array<int, array{key: string, operator: string, value: string}>  $constraints
     * @return array<int, array{key: string, operator: string, value: string}>
     */
    private function sortConstraints(array $constraints): array
    {
        usort($constraints, function (array $left, array $right): int {
            return [$left['key'], $left['operator'], $left['value']] <=> [$right['key'], $right['operator'], $right['value']];
        });

        return $constraints;
    }
}
