<?php

namespace zennit\ABAC\Services\Evaluators;

use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Models\CollectionCondition;
use zennit\ABAC\Strategies\OperatorFactory;

readonly class AbacConditionEvaluator
{
    public function __construct(
        private OperatorFactory $operatorFactory,
    ) {
    }

    /**
     * Evaluate a policy condition against attributes.
     *
     * @param  CollectionCondition  $condition  The condition to evaluate
     * @param  AttributeCollection  $attributes  The attributes to evaluate against
     *
     * @throws UnsupportedOperatorException If an operator is not supported
     * @return bool True if condition is met
     */
    public function evaluate(CollectionCondition $condition, AttributeCollection $attributes): bool
    {
        if ($condition->attributes->isEmpty()) {
            return false;
        }

        // Use condition's operator to evaluate attributes
        $operator = $this->operatorFactory->create($condition->operator);

        return $operator->evaluate(
            $condition->attributes->map(function ($attribute) use ($attributes) {
                $attributeOperator = $this->operatorFactory->create($attribute->operator);

                return $attributeOperator->evaluate($attributes->get($attribute->attribute_name), $attribute->attribute_value);
            })->toArray(),
            $attributes
        );
    }
}
