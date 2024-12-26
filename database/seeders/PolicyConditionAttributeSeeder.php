<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Enums\Operators\ListOperators;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\PolicyCollection;
use zennit\ABAC\Models\PolicyCondition;
use zennit\ABAC\Models\PolicyConditionAttribute;

class PolicyConditionAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributeMap = [
            'System Admin Full Access' => [
                ['role', 'system_admin', ArithmeticOperators::EQUALS],
            ],
            'Organization Admin Access' => [
                ['role', 'org_admin', ArithmeticOperators::EQUALS],
                ['organizationId', '1', ListOperators::IN],
            ],
            'Team Member Task Access' => [
                ['role', 'team_member,team_lead', ListOperators::IN],
                ['teamId', '1', ArithmeticOperators::EQUALS],
                ['organizationId', '1', ArithmeticOperators::EQUALS],
            ],
            'Senior Team Member Task Deletion' => [
                ['role', 'team_member', ArithmeticOperators::EQUALS],
                ['experience', '2', 'greater_than_equals'],
                ['teamId', '1', ArithmeticOperators::EQUALS],
            ],
            'Project View Access' => [
                ['visibility', 'public,internal', ListOperators::IN],
                ['teamId', '1', ArithmeticOperators::EQUALS],
                ['status', 'active', ArithmeticOperators::EQUALS],
            ],
        ];

        foreach ($attributeMap as $policyName => $attributes) {
            $policy = Policy::where('name', $policyName)->first();
            if (!$policy) {
                continue;
            }

            $collection = PolicyCollection::where('policy_id', $policy->id)->first();
            if (!$collection) {
                continue;
            }

            $condition = PolicyCondition::where('policy_collection_id', $collection->id)->first();
            if (!$condition) {
                continue;
            }

            foreach ($attributes as [$name, $value, $operation]) {
                PolicyConditionAttribute::updateOrCreate(
                    [
                        'policy_condition_id' => $condition->id,
                        'attribute_name' => $name,
                    ],
                    [
                        'attribute_value' => $value,
                        'operator' => $operation,
                    ]
                );
            }
        }
    }
}
