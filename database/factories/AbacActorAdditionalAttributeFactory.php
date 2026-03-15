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
            '_id' => (string) $this->faker->numberBetween(1, 10),
            'key' => $this->faker->word,
            'value' => $this->faker->word,
        ];
    }
}
