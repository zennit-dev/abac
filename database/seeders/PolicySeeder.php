<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Models\AbacPolicy;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        $policy_path = resource_path(config('abac.seeders.policy_file_path'));

        if (!file_exists($policy_path)) {
            $this->command->error("Permission file not found at path: $policy_path");

            return;
        }

        $abac = json_decode(file_get_contents($policy_path), true);

        if (!is_array($abac)) {
            $this->command->error('Invalid JSON structure in permission file.');

            return;
        }

        if (isset($abac['policies']) && is_array($abac['policies'])) {
            foreach ($abac['policies'] as $permission) {
                AbacPolicy::create($permission);
            }
        }
        if (isset($abac['chains']) && is_array($abac['chains'])) {
            foreach ($abac['chains'] as $condition) {
                AbacChain::create($condition);
            }
        }
        if (isset($abac['checks']) && is_array($abac['checks'])) {
            foreach ($abac['checks'] as $attribute) {
                AbacCheck::create($attribute);
            }
        }
    }
}
