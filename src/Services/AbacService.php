<?php

namespace zennit\ABAC\Services;

use Exception;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;
use zennit\ABAC\Contracts\AbacManager;
use zennit\ABAC\Contracts\CacheKeyStrategy;
use zennit\ABAC\Contracts\MetricsCollector;
use zennit\ABAC\Contracts\PolicyRepository;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AccessResult;
use zennit\ABAC\DTO\PermissionGrant;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Logging\AbacAuditLogger;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Services\Evaluators\AbacChainEvaluator;
use zennit\ABAC\Services\Permissions\PermissionManager;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

readonly class AbacService implements AbacManager
{
    use AccessesAbacConfiguration;

    public function __construct(
        private AbacCacheManager $cache,
        private AbacChainEvaluator $evaluator,
        private AbacPerformanceMonitor $monitor,
        private AbacAuditLogger $logger,
        private PolicyRepository $policies,
        private CacheKeyStrategy $cacheKeyStrategy,
        private MetricsCollector $metrics,
        private PermissionManager $permissions,
    ) {}

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>|string  $constraints
     *
     * @throws Throwable
     */
    public function addPermission(string $method, string $resource, array|string $constraints): PermissionGrant
    {
        return $this->permissions->addPermission($method, $resource, $constraints);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, PermissionGrant>
     */
    public function getPermissions(?string $method = null, ?string $resource = null, array $filters = []): Collection
    {
        return $this->permissions->getPermissions($method, $resource, $filters);
    }

    public function getPermission(int $grantId): ?PermissionGrant
    {
        return $this->permissions->getPermission($grantId);
    }

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>|string  $constraints
     *
     * @throws Throwable
     */
    public function updatePermission(int $grantId, array|string $constraints): PermissionGrant
    {
        return $this->permissions->updatePermission($grantId, $constraints);
    }

    public function removePermission(int $grantId): bool
    {
        return $this->permissions->removePermission($grantId);
    }

    /**
     * @param  array<string, mixed>|array<int, array<string, mixed>>|string|null  $constraints
     */
    public function removePermissions(string $method, string $resource, array|string|null $constraints = null): int
    {
        return $this->permissions->removePermissions($method, $resource, $constraints);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function can(AccessContext $context): bool
    {
        return $this->evaluate($context)->can;
    }

    /**
     * Evaluate access for the given context
     *
     * @throws InvalidArgumentException
     */
    public function evaluate(AccessContext $context): AccessResult
    {
        $resourceModel = $context->resource->getModel();
        $operation = $context->method->value.':'.get_class($resourceModel);

        /**
         * @var AccessResult $result
         * @var bool $cacheHit
         * @var float $duration
         */
        [[$result, $cacheHit], $duration] = $this->monitor->measure(function () use ($context): array {
            [$result, $cacheHit] = $this->memoizedEvaluate($context);

            if ($this->getLoggingEnabled()) {
                $level = $result->can ? 'info' : 'warning';
                $this->logger->log($result, $level);
            }

            return [$result, $cacheHit];
        });

        $this->metrics->recordEvaluation($operation, $result->can, $duration, $cacheHit);

        if ($duration > $this->getSlowEvaluationThreshold()) {
            $this->logger->log($result, 'warning');
        }

        return $result;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    /**
     * @return array{0: AccessResult, 1: bool}
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function memoizedEvaluate(AccessContext $context): array
    {
        if (! $this->getCacheEnabled()) {
            return [$this->_evaluate($context), false];
        }
        $cache_key = $this->makeCacheKey($context);

        $cached = $this->cache->get($cache_key);
        if ($cached) {
            $query = $context->resource->newQuery();

            if (! ($cached['can'] ?? false)) {
                return [new AccessResult(
                    $query->whereRaw('1 = 0'),
                    $cached['reason'] ?? null,
                    $context,
                    false,
                ), true];
            }

            $primaryKey = $query->getModel()->getQualifiedKeyName();
            $modelKeys = $cached['model_keys'] ?? [];

            $query = empty($modelKeys)
                ? $query->whereRaw('1 = 0')
                : $query->whereIn($primaryKey, $modelKeys);

            return [new AccessResult(
                $query,
                $cached['reason'] ?? null,
                $context,
                (bool) ($cached['can'] ?? false),
            ), true];
        }

        return [
            $this->cache->remember($cache_key, function () use ($context) {
                return $this->_evaluate($context);
            }),
            false,
        ];
    }

    /**
     * @throws Exception
     */
    private function _evaluate(AccessContext $context): AccessResult
    {
        $resourceModel = $context->resource->getModel();
        $model = get_class($resourceModel);

        $policy = $this->policies->findByMethodAndResource($context->method->value, $model);

        if (! $policy) {
            $this->logger->logPolicyMiss($context);

            if ($this->shouldAllowWhenNoPolicyMatched()) {
                $result = new AccessResult(
                    $context->resource,
                    'No policy provided, access granted by default policy behavior.',
                    $context,
                    true,
                );

                $this->logger->logChainOutcome($context, true, null, null, $result->reason);

                return $result;
            }

            $result = new AccessResult(
                $context->resource->whereRaw('1 = 0'),
                'No policy provided, access denied by default policy behavior.',
                $context,
                false,
            );

            $this->logger->logChainOutcome($context, false, null, null, $result->reason);

            return $result;
        }

        $chain = AbacChain::where('policy_id', $policy->id)->first();

        if (is_null($chain)) {
            $result = new AccessResult(
                $context->resource->whereRaw('1 = 0'),
                'Policy exists but has no chain definition; access denied.',
                $context,
                false,
            );

            $this->logger->logChainOutcome($context, false, $policy->id, null, $result->reason);

            return $result;
        }

        $resourceQuery = $this->evaluator->apply($context->resource, $chain, $context);
        $allowed = $resourceQuery->exists();

        if (! $allowed && $this->isCollectionReadRequest($context)) {
            $allowed = true;
        }

        $this->logger->logChainOutcome($context, $allowed, $policy->id, $chain->id);

        return new AccessResult(
            $resourceQuery,
            null,
            $context,
            $allowed,
        );
    }

    private function isCollectionReadRequest(AccessContext $context): bool
    {
        if ($context->method !== PolicyMethod::READ) {
            return false;
        }

        $model = $context->resource->getModel();
        $primaryKeyCandidates = $this->getPrimaryKeyCandidates($model);
        $primaryKeyColumns = array_values(array_unique(array_merge(
            $primaryKeyCandidates,
            array_map(
                static fn (string $key): string => $model->qualifyColumn($key),
                $primaryKeyCandidates,
            ),
        )));

        return ! $this->queryHasPrimaryKeyConstraint($context->resource->getQuery()->wheres, $primaryKeyColumns);
    }

    /**
     * @param  array<int, mixed>  $wheres
     * @param  array<int, string>  $primaryKeyColumns
     */
    private function queryHasPrimaryKeyConstraint(array $wheres, array $primaryKeyColumns): bool
    {
        foreach ($wheres as $where) {
            if (! is_array($where)) {
                continue;
            }

            $column = $where['column'] ?? null;

            if (is_string($column) && in_array($column, $primaryKeyColumns, true)) {
                return true;
            }

            if (($where['type'] ?? null) !== 'Nested') {
                continue;
            }

            $nested = $where['query'] ?? null;

            $nestedWheres = is_object($nested) && property_exists($nested, 'wheres')
                ? ($nested->wheres ?? [])
                : [];

            if ($this->queryHasPrimaryKeyConstraint($nestedWheres, $primaryKeyColumns)) {
                return true;
            }
        }

        return false;
    }

    private function makeCacheKey(AccessContext $context): string
    {
        return $this->cacheKeyStrategy->make($context, $this->getCacheIncludeContext());
    }
}
