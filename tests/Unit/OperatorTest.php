<?php

namespace zennit\ABAC\Tests\Unit;

use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Strategies\OperatorFactory;
use zennit\ABAC\Tests\TestCase;

class OperatorTest extends TestCase
{
    private OperatorFactory $factory;

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_equals_operator(): void
    {
        $operator = $this->factory->create(PolicyOperators::EQUALS->value);

        $this->assertTrue($operator->evaluate('test', 'test'));
        $this->assertFalse($operator->evaluate('test', 'other'));
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_greater_than_operator(): void
    {
        $operator = $this->factory->create(PolicyOperators::GREATER_THAN->value);

        $this->assertTrue($operator->evaluate(5, 3));
        $this->assertFalse($operator->evaluate(3, 5));
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_in_operator(): void
    {
        $operator = $this->factory->create(PolicyOperators::IN->value);

        $this->assertTrue($operator->evaluate('test', ['test', 'other']));
        $this->assertFalse($operator->evaluate('missing', ['test', 'other']));
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_contains_operator(): void
    {
        $operator = $this->factory->create(PolicyOperators::CONTAINS->value);

        $this->assertTrue($operator->evaluate(['test', 'other'], 'test'));
        $this->assertFalse($operator->evaluate(['test', 'other'], 'missing'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new OperatorFactory();
    }
}
