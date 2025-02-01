<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Enums\Operators\AllOperators;
use zennit\ABAC\Models\CollectionCondition;
use zennit\ABAC\Models\ConditionAttribute;

class ConditionAttributeFactory extends Factory
{
    protected $model = ConditionAttribute::class;

    public function definition(): array
    {
        return [
            'collection_condition_id' => CollectionCondition::factory()->create()->id,
            'operator' => $this->faker->randomElement(AllOperators::values()),
            'attribute_name' => $this->faker->word,
            'attribute_value' => $this->faker->word,
        ];
    }
}
