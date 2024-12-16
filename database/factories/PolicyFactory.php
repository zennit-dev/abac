<?php

namespace zennit\ABAC\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use zennit\ABAC\Models\Permission;
use zennit\ABAC\Models\Policy;

class PolicyFactory extends Factory
{
    protected $model = Policy::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'permission_id' => Permission::factory()->create()->id,
        ];
    }
}
