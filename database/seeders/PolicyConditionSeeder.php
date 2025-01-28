<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Models\CollectionCondition;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\PolicyCollection;

class PolicyConditionSeeder extends Seeder
{
    public function run(): void
    {
        $policyConditions = [
            'System Admin Full Access' => ArithmeticOperators::EQUALS,
            'Organization Admin Access' => LogicalOperators::AND,
            'Team Member Task Access' => LogicalOperators::AND,
            'Senior Team Member Task Deletion' => LogicalOperators::AND,
            'Project View Access' => LogicalOperators::OR,
        ];

        foreach ($policyConditions as $policyName => $operator) {
            $policy = Policy::where('name', $policyName)->first();
            if ($policy) {
                $collection = PolicyCollection::where('policy_id', $policy->id)->first();
                if ($collection) {
                    CollectionCondition::firstOrCreate([
                        'policy_collection_id' => $collection->id,
                        'operator' => $operator->value,
                    ]);
                }
            }
        }
    }
}
