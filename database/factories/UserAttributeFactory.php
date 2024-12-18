<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Models\UserAttribute;

class UserAttributeFactory extends Factory
{
    protected $model = UserAttribute::class;

    public function definition(): array
    {
        return [
            config('abac.tables.user_attributes.name', 'name') => 'user_attributes',
            config('abac.tables.user_attributes.subject_type_column', 'subject_type') => 'App\\Models\\User',
            config('abac.tables.user_attributes.subject_id_column', 'subject_id') => $this->faker->numberBetween(1, 100),
            config('abac.tables.user_attributes.attribute_name_column', 'attribute_name') => $this->faker->word,
            config('abac.tables.user_attributes.attribute_value_column', 'attribute_value') => $this->faker->word,
        ];
    }
}
