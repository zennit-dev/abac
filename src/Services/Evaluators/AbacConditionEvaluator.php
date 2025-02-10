<?php

namespace zennit\ABAC\Services\Evaluators;

use zennit\ABAC\DTO\AccessContext;
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
     * @param  AccessContext  $context  The access context for contextual evaluation
     *
     * @throws UnsupportedOperatorException If an operator is not supported
     * @return bool True if condition is met
     */
    public function evaluate(
        CollectionCondition $condition,
        AttributeCollection $attributes,
        AccessContext $context
    ): bool {
        if ($condition->attributes->isEmpty()) {
            return false;
        }

        $operator = $this->operatorFactory->create($condition->operator);

        $checks =  $condition->attributes->map(
            fn ($attribute) => $this->operatorFactory->create($attribute->operator)->evaluate(
                $attributes->get($attribute->attribute_name),
                $attribute->attribute_value,
                $context
            )
        )->toArray();

        return $operator->evaluate(
            $checks,
            $attributes,
            $context
        );
    }
}
