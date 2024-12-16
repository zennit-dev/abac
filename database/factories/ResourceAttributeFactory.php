<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Models\ResourceAttribute;

class ResourceAttributeFactory extends Factory
{
    protected $model = ResourceAttribute::class;

    public function definition(): array
    {
        return [
            'resource' => $this->faker->word,
            'attribute_name' => $this->faker->word,
            'attribute_value' => $this->faker->word,
        ];
    }
}
