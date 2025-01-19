<?php

namespace zennit\ABAC\Services;

use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\EvaluationResult;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Services\Evaluators\AbacPolicyEvaluator;
use zennit\ABAC\Traits\AbacHasConfigurations;
use zennit\ABAC\Validators\AccessContextValidator;

readonly class AbacService
{
    use AbacHasConfigurations;

    public function __construct(
        private AbacCacheManager       $cache,
        private AbacAttributeLoader    $attributeLoader,
        private AbacPolicyEvaluator    $evaluator,
        private AuditLogger            $logger,
        private AbacPerformanceMonitor $monitor
    ) {
    }

    /**
     * Check if a subject has permission to perform an operation on a resource.
     *
     * @param AccessContext $context The access context containing subject, resource, and operation
     *
     * @throws ValidationException If the context is invalid
     * @throws InvalidArgumentException If cache operations fail
     * @return bool True if access is granted, false otherwise
     */
    public function can(AccessContext $context): bool
    {
        return $this->evaluate($context)->granted;
    }

    /**
     * Evaluate an access request and return detailed results.
     *
     * @param AccessContext $context The access context to evaluate
     *
     * @throws ValidationException If the context is invalid
     * @throws InvalidArgumentException If cache operations fail
     * @return EvaluationResult The detailed evaluation result
     */
    public function evaluate(AccessContext $context): EvaluationResult
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
     * Perform the actual access evaluation logic.
     *
     * @param AccessContext $context The access context to evaluate
     *
     * @throws ValidationException If the context is invalid
     * @throws InvalidArgumentException If cache operations fail
     * @return EvaluationResult The evaluation result with detailed information
     */
    private function evaluateAccess(AccessContext $context): EvaluationResult
    {
        $attributes = $this->attributeLoader->loadForContext($context);

        if ($this->getStrictValidation()) {
            $this->validateContext($context);
        }

        return $this->evaluator->evaluate($context, $attributes);
    }

    /**
     * Validate the access context.
     *
     * @param AccessContext $context The context to validate
     *
     * @throws ValidationException If the context is invalid
     */
    private function validateContext(AccessContext $context): void
    {
        app(AccessContextValidator::class)->validate($context);
    }
}
