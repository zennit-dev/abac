<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

class UserAttributeFactory extends Factory
{
    use ZennitAbacHasConfigurations;

    protected $model = UserAttribute::class;

    public function definition(): array
    {
        return [
            'subject_type' => $this->getUserAttributeSubjectType(),
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
