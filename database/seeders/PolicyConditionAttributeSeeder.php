<?php

namespace zennit\ABAC\Database\Seeders;

use Illuminate\Database\Seeder;
use zennit\ABAC\Enums\Operators\ArithmeticOperators;
use zennit\ABAC\Models\CollectionCondition;
use zennit\ABAC\Models\ConditionAttribute;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\PolicyCollection;

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
                ['organizationId', '1', ArithmeticOperators::EQUALS],
            ],
            'Team Member Task Access' => [
                ['role', 'team_member,team_lead', ArithmeticOperators::EQUALS],
                ['experience', '1', ArithmeticOperators::GREATER_THAN_EQUALS],
                ['teamId', '1', ArithmeticOperators::EQUALS],
                ['organizationId', '1', ArithmeticOperators::EQUALS],
                ['teamId', '1', ArithmeticOperators::EQUALS],
                ['organizationId', '1', ArithmeticOperators::EQUALS],
            ],
            'Senior Team Member Task Deletion' => [
                ['role', 'team_member', ArithmeticOperators::EQUALS],
                ['experience', '2', ArithmeticOperators::GREATER_THAN_EQUALS],
                ['teamId', '1', ArithmeticOperators::EQUALS],
                ['organizationId', '1', ArithmeticOperators::EQUALS],
                ['teamId', '1', ArithmeticOperators::EQUALS],
            ],
            'Project View Access' => [
                ['visibility', 'public', ArithmeticOperators::EQUALS],
                ['visibility', 'private', ArithmeticOperators::EQUALS],
                ['organizationId', '1', ArithmeticOperators::EQUALS],
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

            $condition = CollectionCondition::where('policy_collection_id', $collection->id)->first();
            if (!$condition) {
                continue;
            }

            foreach ($attributes as [$name, $value, $operation]) {
                ConditionAttribute::updateOrCreate(
                    [
                        'collection_condition_id' => $condition->id,
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
