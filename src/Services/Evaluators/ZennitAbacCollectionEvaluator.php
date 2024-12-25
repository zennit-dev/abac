<?php

namespace zennit\ABAC\Services\Evaluators;

use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Models\PolicyCollection;
use zennit\ABAC\Models\PolicyCondition;
use zennit\ABAC\Strategies\OperatorFactory;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

readonly class ZennitAbacCollectionEvaluator
{
    use ZennitAbacHasConfigurations;

    public function __construct(
        private OperatorFactory $operatorFactory,
        private ZennitAbacConditionEvaluator $conditionEvaluator,
    ) {
    }

    /**
     * Evaluate a policy collection against attributes.
     *
     * @param PolicyCollection $collection The collection to evaluate
     * @param AttributeCollection $attributes The attributes to evaluate against
     *
     * @throws UnsupportedOperatorException If an operator is not supported
     * @return bool True if collection conditions are met
     */
    public function evaluate(PolicyCollection $collection, AttributeCollection $attributes): bool
    {
        if ($collection->conditions->isEmpty()) {
            return false;
        }

        $operator = $this->operatorFactory->create($collection->operator);

        return $operator->evaluate(
            $collection->conditions->map(
                fn (PolicyCondition $condition) => $this->conditionEvaluator->evaluate($condition, $attributes)
            )->toArray(),
            null
        );
    }
}
