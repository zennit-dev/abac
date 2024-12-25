<?php

namespace zennit\ABAC\Services;

use Illuminate\Support\Collection;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\PolicyCondition;
use zennit\ABAC\Strategies\OperatorFactory;
use zennit\ABAC\Traits\HasConfigurations;

readonly class ZennitAbacConditionEvaluator
{
    use HasConfigurations;

    public function __construct(
        private OperatorFactory $operatorFactory,
    ) {
    }

    /**
     * Evaluates a collection of policies and returns matching ones
     */
    public function evaluatePolicies(Collection $policies, AttributeCollection $attributes): array
    {
        return $policies->filter(function (Policy $policy) use ($attributes) {
            // A policy matches if all its conditions evaluate to true
            return $policy->conditions->every(
                fn (PolicyCondition $condition) => $this->evaluateCondition($condition, $attributes)
            );
        })->all();
    }

    /**
     * Evaluates a single condition
     *
     * @throws UnsupportedOperatorException
     */
    private function evaluateCondition(PolicyCondition $condition, AttributeCollection $attributes): bool
    {
        $operator = $this->operatorFactory->create($condition->operator);

        if ($condition->attributes->isEmpty()) {
            return false;
        }

        foreach ($condition->attributes as $conditionAttribute) {
            $attributeValue = $attributes->get($conditionAttribute->attribute_name);

            if ($attributeValue === null ||
                !$operator->evaluate($attributeValue, $conditionAttribute->attribute_value)) {
                return false;
            }
        }

        return true;
    }
}
