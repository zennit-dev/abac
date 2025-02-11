<?php

namespace zennit\ABAC\Services\Evaluators;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Strategies\OperatorFactory;

readonly class AbacCheckEvaluator
{
    public function __construct(
        private OperatorFactory $operatorFactory,
    ) {
    }

    /**
     * Evaluate a policy condition against attributes.
     *
     * @param AbacCheck $check The condition to evaluate
     * @param AccessContext $context The access context for contextual evaluation
     *
     * @throws UnsupportedOperatorException If an operator is not supported
     * @return bool True if condition is met
     */
    public function evaluate(
        AbacCheck $check,
        AccessContext $context
    ): bool {
        $operator = $this->operatorFactory->create($check->operator);

        return $operator->evaluate(
            values: $context->subject[$check->context_accessor],
            against: $check->value,
            context: $context
        );
    }
}
