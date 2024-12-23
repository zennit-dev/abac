<?php

namespace zennit\ABAC\Strategies;

use InvalidArgumentException;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Strategies\Operators\AndOperator;
use zennit\ABAC\Strategies\Operators\ContainsOperator;
use zennit\ABAC\Strategies\Operators\EndsWithOperator;
use zennit\ABAC\Strategies\Operators\EqualsOperator;
use zennit\ABAC\Strategies\Operators\GreaterThanEqualsOperator;
use zennit\ABAC\Strategies\Operators\GreaterThanOperator;
use zennit\ABAC\Strategies\Operators\InOperator;
use zennit\ABAC\Strategies\Operators\LessThanEqualsOperator;
use zennit\ABAC\Strategies\Operators\LessThanOperator;
use zennit\ABAC\Strategies\Operators\NotContainsOperator;
use zennit\ABAC\Strategies\Operators\NotEndsWithOperator;
use zennit\ABAC\Strategies\Operators\NotEqualsOperator;
use zennit\ABAC\Strategies\Operators\NotInOperator;
use zennit\ABAC\Strategies\Operators\NotOperator;
use zennit\ABAC\Strategies\Operators\NotStartsWithOperator;
use zennit\ABAC\Strategies\Operators\OrOperator;
use zennit\ABAC\Strategies\Operators\StartsWithOperator;
use zennit\ABAC\Traits\HasConfigurations;

class OperatorFactory
{
    use HasConfigurations;

    private array $operators;

    public function __construct()
    {
        $this->operators = [
            'equals' => new EqualsOperator(),
            'not_equals' => new NotEqualsOperator(),
            'greater_than' => new GreaterThanOperator(),
            'greater_than_equals' => new GreaterThanEqualsOperator(),
            'less_than' => new LessThanOperator(),
            'less_than_equals' => new LessThanEqualsOperator(),
            'in' => new InOperator(),
            'not_in' => new NotInOperator(),
            'contains' => new ContainsOperator(),
            'not_contains' => new NotContainsOperator(),
            'starts_with' => new StartsWithOperator(),
            'not_starts_with' => new NotStartsWithOperator(),
            'ends_with' => new EndsWithOperator(),
            'not_ends_with' => new NotEndsWithOperator(),
            'and' => new AndOperator(),
            'or' => new OrOperator(),
            'not' => new NotOperator(),
        ];

        $this->registerCustomOperators();
        $this->removeDisabledOperators();
    }

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

    private function removeDisabledOperators(): void
    {
        foreach ($this->getDisabledOperators() as $operator) {
            unset($this->operators[$operator]);
        }
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function create(string $operator): OperatorInterface
    {
        if (!isset($this->operators[$operator]) || in_array($operator, $this->getDisabledOperators())) {
            throw new UnsupportedOperatorException("Operator '$operator' is not supported");
        }

        return $this->operators[$operator];
    }
}
