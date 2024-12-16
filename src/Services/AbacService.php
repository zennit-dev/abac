<?php

namespace zennit\ABAC\Services;

use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\Contracts\AbacServiceInterface;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Logging\AuditLogger;
use zennit\ABAC\Models\UserAttribute;

readonly class AbacService implements AbacServiceInterface
{
    private array $config;

    public function __construct(
        private PolicyEvaluator $evaluator,
        private CacheService $cache,
        private AuditLogger $logger,
        private PerformanceMonitor $monitor,
        array $config
    ) {
        $this->config = $config;
    }

    /**
     * @throws InvalidArgumentException
     * @throws UnsupportedOperatorException
     */
    public function evaluate(AccessContext $context): PolicyEvaluationResult
    {
        return $this->withPerformanceMonitoring(
            function () use ($context) {
                $attributes = $this->getSubjectAttributes($context->subject);

                $result = $this->config['cache']['enabled']
                    ? $this->evaluateCached($context, $attributes)
                    : $this->evaluator->evaluate($context, $attributes);

                if ($this->config['logging']['enabled'] &&
                    $this->config['logging']['events']['access_evaluated']) {
                    $this->logger->logAccess($context, $result->granted);
                }

                return $result;
            }
        );
    }

    private function withPerformanceMonitoring(callable $callback)
    {
        if ($this->config['performance']['logging_enabled']) {
            $this->monitor->start('policy_evaluation');
        }

        $result = $callback();

        if ($this->config['performance']['logging_enabled']) {
            $duration = $this->monitor->end('policy_evaluation');

            if ($duration > $this->config['performance']['thresholds']['slow_evaluation']) {
                $this->logger->logPerformanceIssue('Slow policy evaluation', [
                    'duration' => $duration,
                ]);
            }
        }

        return $result;
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

    /**
     * @throws InvalidArgumentException
     * @throws UnsupportedOperatorException
     * @return mixed
     */
    private function evaluateCached(AccessContext $context, AttributeCollection $attributes): PolicyEvaluationResult
    {
        $cacheKey = "policy_evaluation:{$context->resource}:{$context->operation}";

        return $this->cache->remember($cacheKey, function () use ($context, $attributes) {
            return $this->evaluator->evaluate($context, $attributes);
        });
    }
}
