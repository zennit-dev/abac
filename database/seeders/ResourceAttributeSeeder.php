<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Models\ResourceAttribute;

class ResourceAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $resourcePath = resource_path(config('abac.seeders.resource_attribute_path'));
        $resourceAttributes = json_decode(file_get_contents($resourcePath), true);

        foreach ($resourceAttributes as $attr) {
            ResourceAttribute::create($attr);
        }
    }
}
