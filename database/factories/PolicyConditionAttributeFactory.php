<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Models\CollectionCondition;
use zennit\ABAC\Models\ConditionAttribute;

class PolicyConditionAttributeFactory extends Factory
{
    protected $model = ConditionAttribute::class;

    public function definition(): array
    {
        return [
            'condition_attribute_id' => CollectionCondition::factory()->create()->id,
            'attribute_name' => $this->faker->word,
            'attribute_value' => $this->faker->word,
        ];
    }
}
