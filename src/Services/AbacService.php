<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Traits\HasConfigurations;
use zennit\ABAC\Validators\AccessContextValidator;

readonly class AbacService
{
    use HasConfigurations;

    public function __construct(
        private PolicyEvaluator $evaluator,
        private CacheManager $cache,
        private AuditLogger $logger,
        private PerformanceMonitor $monitor,
        private AttributeLoader $attributeLoader,
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
     * @throws ValidationException
     */
    private function validateContext(AccessContext $context): void
    {
        app(AccessContextValidator::class)->validate($context);
    }
}
