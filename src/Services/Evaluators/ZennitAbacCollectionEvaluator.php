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
    ) {}

	/**
	 * @throws UnsupportedOperatorException
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
