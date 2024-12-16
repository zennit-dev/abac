<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Models\PolicyCondition;
use zennit\ABAC\Strategies\OperatorFactory;

readonly class ConditionEvaluator
{
    public function __construct(
        private OperatorFactory $operatorFactory
    ) {
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function evaluate(PolicyCondition $condition, AttributeCollection $attributes): bool
    {
        $operator = $this->operatorFactory->create($condition->operator);

        // If condition has no attributes, return false
        if ($condition->attributes->isEmpty()) {
            return false;
        }

        foreach ($condition->attributes as $conditionAttribute) {
            $attributeValue = $attributes->get($conditionAttribute->attribute_name);

            if ($attributeValue === null) {
                return false;
            }

            if (!$operator->evaluate($attributeValue, $conditionAttribute->attribute_value)) {
                return false;
            }
        }

        return true;
    }
}
