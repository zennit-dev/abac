<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Models\AbacObjectAdditionalAttribute;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

class AbacObjectAdditionalAttributesFactory extends Factory
{
    use AccessesAbacConfiguration;

    protected $model = AbacObjectAdditionalAttribute::class;

    public function definition(): array
    {
        return [
            'subject_type' => $this->getObjectAdditionalAttributes(),
            'subject_id' => $this->faker->numberBetween(1, 10),
            'attribute_name' => $this->faker->word,
            'attribute_value' => $this->faker->word,
        ];
    }

    public function forSubject(string $subjectType): self
    {
        return $this->state([
            'subject_type' => $subjectType,
        ]);
    }
}
