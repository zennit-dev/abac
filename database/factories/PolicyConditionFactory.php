<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Enums\PolicyOperators;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\PolicyCondition;

class PolicyConditionFactory extends Factory
{
    protected $model = PolicyCondition::class;

    public function definition(): array
    {
        return [
            'operator' => $this->faker->randomElement(PolicyOperators::values()),
            'policy_id' => Policy::factory()->create()->id,
        ];
    }
}
