<?php

namespace zennit\ABAC\Services;

use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Contracts\AbacManager;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AccessResult;
use zennit\ABAC\Logging\AbacAuditLogger;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacPolicy;
use zennit\ABAC\Services\Evaluators\AbacChainEvaluator;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

readonly class AbacService implements AbacManager
{
    use AccessesAbacConfiguration;

    public function __construct(
        private AbacCacheManager $cache,
        private AbacChainEvaluator $evaluator,
        private AbacPerformanceMonitor $monitor,
        private AbacAuditLogger $logger
    ) {
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
        $operation = $context->method->value . ':' . get_class($context->subject->getModel());

        /**
         * @var AccessResult $result
         * @var float $duration
         */
        [$result, $duration] = $this->monitor->measure($operation, function () use ($context): AccessResult {
            $result = $this->memoizedEvaluate($context);

            if ($this->getLoggingEnabled()) {
                $level = $result->can ? 'info' : 'warning';
                $this->logger->log($result, $level);
            }

            return $result;
        });

        if ($duration > $this->getSlowEvaluationThreshold()) {
            $this->logger->log($result, 'warning');
        }

        return $result;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function memoizedEvaluate(AccessContext $context): AccessResult
    {
        if (!$this->getCacheEnabled()) {
            return $this->_evaluate($context);
        }
        $cache_key = $context->method->value . ':' . get_class($context->subject->getModel());

        $cached = $this->cache->get($cache_key);
        if ($cached) {
            $query = $context->subject->newQuery();
            foreach ($cached['bindings'] as $key => $binding) {
                $query->whereRaw('id = ?', [$binding]);
            }

            return new AccessResult(
                $query,
                $cached['reason'],
                $context
            );
        }

        return $this->cache->remember($cache_key, function () use ($context) {
            return $this->_evaluate($context);
        });
    }

    /**
     * @param AccessContext $context
     *
     * @throws Exception
     * @return AccessResult
     */
    private function _evaluate(AccessContext $context): AccessResult
    {
        $model = get_class($context->subject->getModel());

        $policy = AbacPolicy::where('method', $context->method->value)
            ->where('resource', $model)
            ->first();

        if (!$policy) {
            return new AccessResult($context->subject, 'No policy provided, full access granted.', $context);
        }

        $chain = AbacChain::wherePolicyId($policy->id)->first();
        $subject_query = $this->evaluator->apply($context->subject, $chain, $context);

        return new AccessResult($subject_query, null, $context);
    }
}
