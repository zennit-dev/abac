<?php

namespace zennit\ABAC\Tests\Unit\Strategies;

use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Strategies\OperatorFactory;
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
use zennit\ABAC\Strategies\Operators\NotStartsWithOperator;
use zennit\ABAC\Strategies\Operators\OrOperator;
use zennit\ABAC\Strategies\Operators\StartsWithOperator;
use zennit\ABAC\Tests\TestCase;

class OperatorFactoryTest extends TestCase
{
    private OperatorFactory $factory;

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_creates_operators(): void
    {
        $type = PolicyOperators::EQUALS->value;
        $expectedClass = EqualsOperator::class;
        $operator = $this->factory->create($type);
        $this->assertInstanceOf($expectedClass, $operator);
    }

    public function test_throws_exception_for_invalid_operator(): void
    {
        $this->expectException(UnsupportedOperatorException::class);
        $this->factory->create('invalid_operator');
    }

    public function operatorProvider(): array
    {
        return [
            [PolicyOperators::EQUALS->value, EqualsOperator::class],
            [PolicyOperators::NOT_EQUALS->value, NotEqualsOperator::class],
            [PolicyOperators::GREATER_THAN->value, GreaterThanOperator::class],
            [PolicyOperators::GREATER_THAN_EQUALS->value, GreaterThanEqualsOperator::class],
            [PolicyOperators::LESS_THAN->value, LessThanOperator::class],
            [PolicyOperators::LESS_THAN_EQUALS->value, LessThanEqualsOperator::class],
            [PolicyOperators::IN->value, InOperator::class],
            [PolicyOperators::NOT_IN->value, NotInOperator::class],
            [PolicyOperators::CONTAINS->value, ContainsOperator::class],
            [PolicyOperators::NOT_CONTAINS->value, NotContainsOperator::class],
            [PolicyOperators::STARTS_WITH->value, StartsWithOperator::class],
            [PolicyOperators::NOT_STARTS_WITH->value, NotStartsWithOperator::class],
            [PolicyOperators::ENDS_WITH->value, EndsWithOperator::class],
            [PolicyOperators::NOT_ENDS_WITH->value, NotEndsWithOperator::class],
            [PolicyOperators::AND->value, AndOperator::class],
            [PolicyOperators::OR->value, OrOperator::class],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new OperatorFactory();
    }
}
