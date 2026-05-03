<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Models\AbacActorAdditionalAttribute;

class AbacActorAdditionalAttributeFactory extends Factory
{
    protected $model = AbacActorAdditionalAttribute::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->word,
            'value' => $this->faker->word,
        ];
    }
}
