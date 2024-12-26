<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Models\ResourceAttribute;

class ResourceAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $resourceAttributes = [
            // Organization attributes
            ['resource' => 'organizations', 'attribute_name' => 'visibility', 'attribute_value' => 'private'],
            ['resource' => 'organizations', 'attribute_name' => 'status', 'attribute_value' => 'active'],

            // Project attributes
            ['resource' => 'projects', 'attribute_name' => 'visibility', 'attribute_value' => 'team'],
            ['resource' => 'projects', 'attribute_name' => 'organizationId', 'attribute_value' => '1'],
            ['resource' => 'projects', 'attribute_name' => 'status', 'attribute_value' => 'active'],

            // Task attributes
            ['resource' => 'tasks', 'attribute_name' => 'visibility', 'attribute_value' => 'team'],
            ['resource' => 'tasks', 'attribute_name' => 'organizationId', 'attribute_value' => '1'],
            ['resource' => 'tasks', 'attribute_name' => 'teamId', 'attribute_value' => '1'],
            ['resource' => 'tasks', 'attribute_name' => 'priority', 'attribute_value' => 'high'],

            // Team attributes
            ['resource' => 'teams', 'attribute_name' => 'organizationId', 'attribute_value' => '1'],
            ['resource' => 'teams', 'attribute_name' => 'status', 'attribute_value' => 'active'],
        ];

        foreach ($resourceAttributes as $attr) {
            ResourceAttribute::create($attr);
        }
    }
}
