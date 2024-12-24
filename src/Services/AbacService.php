<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Traits\HasConfigurations;
use zennit\ABAC\Validators\AccessContextValidator;

class AbacService
{
    use HasConfigurations;

    public function __construct(
        private CacheManager $cache,
        private AttributeLoader $attributeLoader,
        private PolicyEvaluator $evaluator,
        private AuditLogger $logger,
	    private PerformanceMonitor $monitor
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function can(AccessContext $context): bool
    {
        return $this->evaluate($context)->granted;
    }

    /**
     * @throws ValidationException
     */
    private function evaluate(AccessContext $context): PolicyEvaluationResult
    {
        return $this->monitor->measure('policy_evaluation', function () use ($context) {
            $cacheKey = "access:{$context->subject->id}:$context->resource:$context->operation";

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
     * @throws ValidationException
     */
    private function evaluateAccess(AccessContext $context): PolicyEvaluationResult
    {
        $attributes = $this->attributeLoader->loadForContext($context);

        if ($this->getStrictValidation()) {
            $this->validateContext($context);
        }

        return $this->evaluator->evaluate($context, $attributes);
    }

    /**
     * Invalidate cache when user attributes change
     */
    public function invalidateUserCache(int $userId, string $userType): void
    {
        $this->cache->forgetUserAttributes($userId, $userType);
    }

    /**
     * Invalidate cache when resource attributes change
     */
    public function invalidateResourceCache(string $resource): void
    {
        $this->cache->forgetResourceAttributes($resource);
    }

    /**
     * @throws ValidationException
     */
    private function validateContext(AccessContext $context): void
    {
        app(AccessContextValidator::class)->validate($context);
    }
}
