<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Enums\Operators\AllOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;

class AbacCheckFactory extends Factory
{
    protected $model = AbacCheck::class;

    public function definition(): array
    {
        return [
            'operator' => $this->faker->randomElement(AllOperators::values(LogicalOperators::cases())),
            'chain_id' => AbacChain::factory()->create()->id,
            'key' => $this->faker->word,
            'value' => $this->faker->word,
        ];
    }
}
