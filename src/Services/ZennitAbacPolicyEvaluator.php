<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Repositories\PolicyRepository;

readonly class ZennitAbacPolicyEvaluator
{
    public function __construct(
        private PolicyRepository             $policyRepository,
        private ZennitAbacConditionEvaluator $conditionEvaluator,
        private ZennitAbacCacheManager $cache
    ) {
    }

    public function evaluate(AccessContext $context, AttributeCollection $attributes): PolicyEvaluationResult
    {
        // Create a unique cache key for this evaluation
        $cacheKey = sprintf(
            'evaluation:%s:%s:%s:%s',
            $context->subject->id,
            $context->resource,
            $context->operation,
            $attributes->hash()
        );

        // Cache the evaluation result
        return $this->cache->rememberPolicyEvaluation($cacheKey, function () use ($context, $attributes) {
            $policies = $this->policyRepository->getPoliciesFor($context->resource);

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
        });
    }

}
