<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Enums\Operators\AllOperators;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\PolicyCollection;

class PolicyCollectionFactory extends Factory
{
    protected $model = PolicyCollection::class;

    public function definition(): array
    {
        return [
            'operator' => $this->faker->randomElement(AllOperators::values()),
            'policy_id' => Policy::factory()->create()->id,
        ];
    }
}
