<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Enums\Operators\AllOperators;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacPolicy;

class AbacChainFactory extends Factory
{
    protected $model = AbacChain::class;

    public function definition(): array
    {
        return $this->chainOrPolicy();
    }

    private function chainOrPolicy(): array
    {
        $isChain = $this->faker->boolean();

        return [
            'operator' => $this->faker->randomElement(AllOperators::values()),
            $isChain ? 'chain_id' : 'policy_id' => $isChain
                ? AbacChain::factory()->create()->id
                : AbacPolicy::factory()->create()->id,
        ];
    }
}
