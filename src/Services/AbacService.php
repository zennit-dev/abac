<?php

namespace zennit\ABAC\Services;

use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Contracts\AbacServiceInterface;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Models\UserAttribute;

readonly class AbacService implements AbacServiceInterface
{
    public function __construct(
        private PolicyEvaluator $evaluator,
        private CacheService $cache,
        private AuditLogger $logger,
        private PerformanceMonitor $monitor,
        private ConfigurationService $config
    ) {
    }

    /**
     * @throws UnsupportedOperatorException
     * @throws ValidationException
     * @throws InvalidArgumentException
     */
    public function evaluate(AccessContext $context): PolicyEvaluationResult
    {
        if ($this->config->getStrictValidation()) {
            $this->validateContext($context);
        }

        return $this->withPerformanceMonitoring(
            function () use ($context) {
                $attributes = $this->getSubjectAttributes($context->subject);
                $result = $this->evaluateWithCache($context, $attributes);

                $this->logger->logAccess($context, $result->granted);

                return $result;
            }
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws UnsupportedOperatorException
     */
    private function evaluateWithCache(AccessContext $context, AttributeCollection $attributes): PolicyEvaluationResult
    {
        $cacheKey = sprintf(
            'evaluation:%s:%s:%s',
            $context->resource,
            $context->operation,
            $attributes->hash()
        );

        return $this->cache->remember(
            $cacheKey,
            fn () => $this->evaluator->evaluate($context, $attributes)
        );
    }

    private function withPerformanceMonitoring(callable $callback)
    {
        if ($this->config->getPerformanceLoggingEnabled()) {
            $this->monitor->start('policy_evaluation');
        }

        $result = $callback();

        if ($this->config->getPerformanceLoggingEnabled()) {
            $this->monitor->end('policy_evaluation');
        }

        return $result;
    }

    /**
     * @throws ValidationException
     */
    private function validateContext(AccessContext $context): void
    {
        $requiredAttributes = $this->config->getRequiredAttributes($context->resource);
        if (empty($requiredAttributes)) {
            return;
        }

        $subjectAttributes = $this->getSubjectAttributes($context->subject);
        $missingAttributes = array_diff($requiredAttributes, array_keys($subjectAttributes->all()));

        if (!empty($missingAttributes)) {
            throw new ValidationException(sprintf(
                'Missing required attributes for resource "%s": %s',
                $context->resource,
                implode(', ', $missingAttributes)
            ));
        }
    }

    private function getSubjectAttributes($subject): AttributeCollection
    {
        $attributes = UserAttribute::where('subject_type', get_class($subject))
            ->where('subject_id', $subject->id)
            ->get()
            ->mapWithKeys(function ($attribute) {
                return [$attribute->attribute_name => $attribute->attribute_value];
            })
            ->all();

        return new AttributeCollection($attributes);
    }
}
