<?php

namespace zennit\ABAC\Services\Evaluators;

use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Models\CollectionCondition;
use zennit\ABAC\Models\PolicyCollection;
use zennit\ABAC\Strategies\OperatorFactory;
use zennit\ABAC\Traits\AbacHasConfigurations;

readonly class AbacCollectionEvaluator
{
    use AbacHasConfigurations;

    public function __construct(
        private OperatorFactory $operatorFactory,
        private AbacConditionEvaluator $conditionEvaluator,
    ) {
    }

    /**
     * Evaluate a policy collection against attributes.
     *
     * @param  PolicyCollection  $collection  The collection to evaluate
     * @param  AttributeCollection  $attributes  The attributes to evaluate against
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
                fn (CollectionCondition $condition) => $this->conditionEvaluator->evaluate($condition, $attributes)
            )->toArray(),
            $attributes
        );
    }
}
