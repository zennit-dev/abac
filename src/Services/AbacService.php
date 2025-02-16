<?php

namespace zennit\ABAC\Services;

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
     */
    private function memoizedEvaluate(AccessContext $context): AccessResult
    {
        if (!$this->getCacheEnabled()) {
            return $this->_evaluate($context);
        }
        $cache_key = 'abac_policies:' . $context->method->value . ':' . get_class($context->subject->getModel());

        return $this->cache->remember($cache_key, function () use ($context) {
            return $this->_evaluate($context);
        });
    }

    /**
     * @param AccessContext $context
     *
     * @return AccessResult
     */
    private function _evaluate(AccessContext $context): AccessResult
    {
        $subject_class = get_class($context->subject->getModel());

        $policy = AbacPolicy::where('method', $context->method->value)
            ->where('resource', $subject_class)
            ->first();

        if (!$policy) {
            return new AccessResult($context->subject, 'No policy provided, full access granted.', $context);
        }

        $chain = AbacChain::wherePolicyId($policy->id)->first();
        $subject_query = $this->evaluator->apply($context->subject, $chain, $context);

        return new AccessResult($subject_query, null, $context);
    }
}
