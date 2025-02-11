<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Models\AbacPolicy;

class AbacPolicyFactory extends Factory
{
    protected $model = AbacPolicy::class;

    public function definition(): array
    {
        return [
            'method' => $this->faker->randomElement(PolicyMethod::values()),
            'resource' => $this->faker->word,
        ];
    }
}
