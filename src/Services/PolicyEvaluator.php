<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Repositories\PolicyRepository;

readonly class PolicyEvaluator
{
    public function __construct(
        private PolicyRepository $policyRepository,
        private ConditionEvaluator $conditionEvaluator
    ) {
    }

    public function evaluate(AccessContext $context, AttributeCollection $attributes): PolicyEvaluationResult
    {
        $policies = $this->policyRepository->getPoliciesFor($context->resource, $context->operation);

        if ($policies->isEmpty()) {
            return new PolicyEvaluationResult(
                granted: false,
                reason: 'No applicable policies found',
                context: [
                    'resource' => $context->resource,
                    'operation' => $context->operation,
                ]
            );
        }

        $matchedPolicies = $this->conditionEvaluator->evaluatePolicies($policies, $attributes);

        return new PolicyEvaluationResult(
            granted: !empty($matchedPolicies),
            reason: !empty($matchedPolicies) ? 'Policy conditions met' : 'No matching policies found',
            context: [
                'resource' => $context->resource,
                'operation' => $context->operation,
                'attributes' => $attributes->toArray(),
            ],
            matchedPolicies: $matchedPolicies
        );
    }
}
