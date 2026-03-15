<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Models\AbacActorAdditionalAttribute;

class ActorAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $resourcePath = resource_path(config('abac.seeders.actor_attribute_path'));

        if (! file_exists($resourcePath)) {
            $this->command->error("Actor attribute file not found at path: $resourcePath");

            return;
        }

        $actorAttributes = json_decode(file_get_contents($resourcePath), true);

        if (! is_array($actorAttributes)) {
            $this->command->error('Invalid JSON structure in actor attribute file.');

            return;
        }

        foreach ($actorAttributes as $attr) {
            AbacActorAdditionalAttribute::create($attr);
        }
    }
}
