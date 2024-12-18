<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Models\PolicyCondition;
use zennit\ABAC\Strategies\OperatorFactory;

readonly class ConditionEvaluator
{
    public function __construct(
        private OperatorFactory $operatorFactory,
        private ConfigurationService $config
    ) {
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function evaluate(PolicyCondition $condition, AttributeCollection $attributes): bool
    {
        if (in_array($condition->operator, $this->config->getDisabledOperators())) {
            throw new UnsupportedOperatorException("Operator '{$condition->operator}' is disabled");
        }

        $operator = $this->operatorFactory->create($condition->operator);
        $result = true;

        if ($condition->attributes->isEmpty()) {
            $result = false;
        } else {
            foreach ($condition->attributes as $conditionAttribute) {
                $attributeValue = $attributes->get($conditionAttribute->attribute_name);

                if ($attributeValue === null ||
                    !$operator->evaluate($attributeValue, $conditionAttribute->attribute_value)) {
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }
}
