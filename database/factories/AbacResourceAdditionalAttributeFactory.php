<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Models\AbacResourceAdditionalAttribute;

class AbacResourceAdditionalAttributeFactory extends Factory
{
    protected $model = AbacResourceAdditionalAttribute::class;

    public function definition(): array
    {
        return [
            'model' => $this->faker->word,
            'key' => $this->faker->word,
            'value' => $this->faker->word,
        ];
    }
}
