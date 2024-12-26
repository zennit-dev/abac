<?php

namespace Database\Seeders\AccessControl;

use Illuminate\Database\Seeder;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\PolicyCollection;

class PolicyCollectionSeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            'System Admin Full Access' => LogicalOperators::AND,
            'Organization Admin Access' => LogicalOperators::AND,
            'Team Member Task Access' => LogicalOperators::OR,
            'Senior Team Member Task Deletion' => LogicalOperators::AND,
            'Project View Access' => LogicalOperators::OR,
        ];

        foreach ($policies as $policyName => $operator) {
            $policy = Policy::where('name', $policyName)->first();
            if ($policy) {
                PolicyCollection::firstOrCreate([
                    'policy_id' => $policy->id,
                    'operator' => $operator->value,
                ]);
            }
        }
    }
}
