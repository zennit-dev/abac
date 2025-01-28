<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Enums\Operators\ListOperators;
use zennit\ABAC\Models\CollectionCondition;

class PolicyConditionFactory extends Factory
{
    protected $model = CollectionCondition::class;

    public function definition(): array
    {
        return [
            'operator' => $this->faker->randomElement(ListOperators::values()),
            'policy_collection_id' => CollectionCondition::factory()->create()->id,
        ];
    }
}
