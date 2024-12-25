<?php

namespace zennit\ABAC\Services;

use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\EvaluationResult;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Services\Evaluators\ZennitAbacPolicyEvaluator;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;
use zennit\ABAC\Validators\AccessContextValidator;

readonly class ZennitAbacService
{
    use ZennitAbacHasConfigurations;

    public function __construct(
        private ZennitAbacCacheManager       $cache,
        private ZennitAbacAttributeLoader    $attributeLoader,
        private ZennitAbacPolicyEvaluator    $evaluator,
        private AuditLogger                  $logger,
        private ZennitAbacPerformanceMonitor $monitor
    ) {
    }

	/**
	 * @throws ValidationException
	 * @throws InvalidArgumentException
	 */
    public function can(AccessContext $context): bool
    {
        return $this->evaluate($context)->granted;
    }

	/**
	 * @throws ValidationException
	 * @throws InvalidArgumentException
	 */
    private function evaluate(AccessContext $context): EvaluationResult
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
     * @throws InvalidArgumentException
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
     * @throws ValidationException
     */
    private function validateContext(AccessContext $context): void
    {
        app(AccessContextValidator::class)->validate($context);
    }
}
