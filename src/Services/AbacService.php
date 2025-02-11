<?php

namespace zennit\ABAC\Services;

use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Contracts\AbacManager;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacPolicy;
use zennit\ABAC\Services\Evaluators\AbacChainEvaluator;
use zennit\ABAC\Traits\AbacHasConfigurations;
use zennit\ABAC\Validators\AccessContextValidator;

readonly class AbacService implements AbacManager
{
    use AbacHasConfigurations;

    public function __construct(
        private AbacCacheManager $cache,
        private AbacAttributeLoader $attributeLoader,
        private AbacChainEvaluator $evaluator,
        private AuditLogger $logger,
        private AbacPerformanceMonitor $monitor
    ) {
    }

    /**
     * AbacCheck if a subject has permission to perform an operation on a resource.
     *
     * @param AccessContext $context The access context containing subject, resource, and operation
     *
     * @throws InvalidArgumentException If cache operations fail
     * @throws UnsupportedOperatorException
     * @throws ValidationException If the context is invalid
     * @return bool True if access is granted, false otherwise
     */
    public function can(AccessContext $context): bool
    {
        return $this->monitor->measure('policy_evaluation', function () use ($context) {
            $cacheKey = "access:{$context->object['id']}:{$context->subject['id']}:$context->method";

            /** @var bool $result */
            $result = $this->cache->remember(
                $cacheKey,
                fn () => $this->evaluateAccess($context)
            );

            if ($this->getLoggingEnabled()) {
                $this->logger->logAccess($context, $result);
            }

            return $result;
        });
    }

    /**
     * Perform the actual access evaluation logic.
     *
     * @param AccessContext $context The access context to evaluate
     *
     * @throws UnsupportedOperatorException
     * @throws ValidationException If the context is invalid
     * @return bool The evaluation result with detailed information
     */
    private function evaluateAccess(AccessContext $context): bool
    {
        if ($this->getStrictValidation()) {
            $this->validateContext($context);
        }

        $policy = AbacPolicy::where('method', $context->method)
            ->where('resource', $context->subject_type)
            ->first();

        $chain = AbacChain::whereIn('policy_id', $policy->id)->first();

        return $this->evaluator->evaluate($chain, $context);
    }

    /**
     * Validate the access context.
     *
     * @param  AccessContext  $context  The context to validate
     *
     * @throws ValidationException If the context is invalid
     */
    private function validateContext(AccessContext $context): void
    {
        app(AccessContextValidator::class)->validate($context);
    }

    public function evaluate(AccessContext $context)
    {
        // todo: return the matched resources
    }
}
