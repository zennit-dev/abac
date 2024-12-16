<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Models\PolicyCondition;
use zennit\ABAC\Models\PolicyConditionAttribute;

class PolicyConditionAttributeFactory extends Factory
{
    protected $model = PolicyConditionAttribute::class;

    public function definition(): array
    {
        return [
            'policy_condition_id' => PolicyCondition::factory()->create()->id,
            'attribute_name' => $this->faker->word,
            'attribute_value' => $this->faker->word,
        ];
    }
}
