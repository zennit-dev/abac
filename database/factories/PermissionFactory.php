<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Enums\RequestMethods;
use zennit\ABAC\Models\Permission;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'resource' => $this->faker->word,
            'operation' => $this->faker->randomElement(RequestMethods::values()),
        ];
    }
}
