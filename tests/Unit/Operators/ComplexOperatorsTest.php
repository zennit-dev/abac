<?php

namespace zennit\ABAC\Tests\Unit\Operators;

use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Strategies\OperatorFactory;
use zennit\ABAC\Tests\TestCase;

class ComplexOperatorsTest extends TestCase
{
    private OperatorFactory $factory;

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_and_operator(): void
    {
        $operator = $this->factory->create(PolicyOperators::AND->value);

        $this->assertTrue($operator->evaluate(true, true));
        $this->assertFalse($operator->evaluate(true, false));
        $this->assertFalse($operator->evaluate(false, false));
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_or_operator(): void
    {
        $operator = $this->factory->create(PolicyOperators::OR->value);

        $this->assertTrue($operator->evaluate(false, true));
        $this->assertFalse($operator->evaluate(false, false));
    }

    /**
     * @throws UnsupportedOperatorException
     */
    public function test_not_operator(): void
    {
        $operator = $this->factory->create(PolicyOperators::NOT->value);

        $this->assertTrue($operator->evaluate(false, null));
        $this->assertFalse($operator->evaluate(true, null));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new OperatorFactory();
    }
}
