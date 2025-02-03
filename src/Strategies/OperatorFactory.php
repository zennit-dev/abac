<?php

namespace zennit\ABAC\Strategies;

use InvalidArgumentException;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Strategies\Contracts\Core\OperatorInterface;
use zennit\ABAC\Strategies\Operators\Arithmetic\EqualsOperator;
use zennit\ABAC\Strategies\Operators\Arithmetic\GreaterThanEqualsOperator;
use zennit\ABAC\Strategies\Operators\Arithmetic\GreaterThanOperator;
use zennit\ABAC\Strategies\Operators\Arithmetic\LessThanEqualsOperator;
use zennit\ABAC\Strategies\Operators\Arithmetic\LessThanOperator;
use zennit\ABAC\Strategies\Operators\Arithmetic\NotEqualsOperator;
use zennit\ABAC\Strategies\Operators\Logical\AndOperator;
use zennit\ABAC\Strategies\Operators\Logical\NotOperator;
use zennit\ABAC\Strategies\Operators\Logical\OrOperator;
use zennit\ABAC\Strategies\Operators\String\ContainsOperator;
use zennit\ABAC\Strategies\Operators\String\EndsWithOperator;
use zennit\ABAC\Strategies\Operators\String\NotContainsOperator;
use zennit\ABAC\Strategies\Operators\String\NotEndsWithOperator;
use zennit\ABAC\Strategies\Operators\String\NotStartsWithOperator;
use zennit\ABAC\Strategies\Operators\String\StartsWithOperator;
use zennit\ABAC\Traits\AbacHasConfigurations;

class OperatorFactory
{
    use AbacHasConfigurations;

    private array $operators;

    public function __construct()
    {
        $this->operators = [
            'equals' => new EqualsOperator(),
            'greater_than' => new GreaterThanOperator(),
            'greater_than_equals' => new GreaterThanEqualsOperator(),
            'less_than_equals' => new LessThanEqualsOperator(),
            'less_than' => new LessThanOperator(),
            'not_equals' => new NotEqualsOperator(),
            'and' => new AndOperator(),
            'or' => new OrOperator(),
            'not' => new NotOperator(),
            'contains' => new ContainsOperator(),
            'ends_with' => new EndsWithOperator(),
            'not_contains' => new NotContainsOperator(),
            'not_starts_with' => new NotStartsWithOperator(),
            'not_ends_with' => new NotEndsWithOperator(),
            'starts_with' => new StartsWithOperator(),
        ];

        $this->registerCustomOperators();
        $this->removeDisabledOperators();
    }

    /**
     * Create an operator instance by its identifier.
     *
     * @param string $operator The operator identifier
     *
     * @throws UnsupportedOperatorException If the operator is not supported
     * @return OperatorInterface The operator instance
     */
    public function create(string $operator): OperatorInterface
    {
        if (!isset($this->operators[$operator]) || in_array($operator, $this->getDisabledOperators())) {
            throw new UnsupportedOperatorException("Operator '$operator' is not supported");
        }

        return $this->operators[$operator];
    }

    /**
     * Register a custom operator.
     *
     * @param string $key The operator identifier
     * @param OperatorInterface $operator The operator instance
     *
     * @throws InvalidArgumentException If key is empty or operator already exists
     */
    public function register(string $key, OperatorInterface $operator): void
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Operator key cannot be empty');
        }

        if (isset($this->operators[$key])) {
            throw new InvalidArgumentException("Operator with key '$key' is already registered");
        }

        $this->operators[$key] = $operator;
    }

    /**
     * Register all custom operators from configuration.
     *
     * @throws InvalidArgumentException If custom operator class is invalid
     */
    private function registerCustomOperators(): void
    {
        foreach ($this->getCustomOperators() as $key => $operatorClass) {
            if (!class_exists($operatorClass)) {
                throw new InvalidArgumentException("Custom operator class '$operatorClass' does not exist");
            }

            if (!is_subclass_of($operatorClass, OperatorInterface::class)) {
                throw new InvalidArgumentException("Custom operator class '$operatorClass' must implement OperatorInterface");
            }

            $this->register($key, new $operatorClass());
        }
    }

    /**
     * Remove disabled operators from the available operators list.
     */
    private function removeDisabledOperators(): void
    {
        foreach ($this->getDisabledOperators() as $operator) {
            unset($this->operators[$operator]);
        }
    }
}
