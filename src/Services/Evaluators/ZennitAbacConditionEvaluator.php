<?php

namespace zennit\ABAC\Services\Evaluators;

use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Models\PolicyCondition;
use zennit\ABAC\Strategies\OperatorFactory;

readonly class ZennitAbacConditionEvaluator
{
    public function __construct(
        private OperatorFactory $operatorFactory,
    ) {
    }

    /**
     * Evaluate a policy condition against attributes.
     *
     * @param PolicyCondition $condition The condition to evaluate
     * @param AttributeCollection $attributes The attributes to evaluate against
     * @return bool True if condition is met
     * @throws UnsupportedOperatorException If an operator is not supported
     */
    public function evaluate(PolicyCondition $condition, AttributeCollection $attributes): bool
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
            null
        );
    }
}
