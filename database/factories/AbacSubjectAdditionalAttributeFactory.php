<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Models\AbacSubjectAdditionalAttribute;

class AbacSubjectAdditionalAttributeFactory extends Factory
{
    protected $model = AbacSubjectAdditionalAttribute::class;

    public function definition(): array
    {
        return [
            'resource' => $this->faker->word,
            'attribute_name' => $this->faker->word,
            'attribute_value' => $this->faker->word,
        ];
    }
}
