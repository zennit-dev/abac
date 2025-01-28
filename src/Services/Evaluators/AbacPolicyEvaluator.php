<?php

namespace zennit\ABAC\Services\Evaluators;

use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\DTO\EvaluationResult;
use zennit\ABAC\Models\Policy;
use zennit\ABAC\Models\PolicyCollection;
use zennit\ABAC\Repositories\PolicyRepository;
use zennit\ABAC\Services\AbacCacheManager;

readonly class AbacPolicyEvaluator
{
    public function __construct(
        private PolicyRepository $policyRepository,
        private AbacCacheManager $cache,
        private AbacCollectionEvaluator $collectionEvaluator
    ) {
    }

    /**
     * Evaluate access based on policies.
     *
     * @param  AccessContext  $context  The access context to evaluate
     * @param  AttributeCollection  $attributes  The attributes to evaluate against
     *
     * @throws InvalidArgumentException If cache operations fail
     * @return EvaluationResult The result of the policy evaluation
     */
    public function evaluate(AccessContext $context, AttributeCollection $attributes): EvaluationResult
    {
        $cacheKey = sprintf(
            'evaluation:%s:%s:%s:%s',
            $context->subject->id,
            $context->resource,
            $context->operation,
            $attributes->hash()
        );

        return $this->cache->rememberPolicyEvaluation($cacheKey, function () use ($context, $attributes) {
            $policies = $this->policyRepository->getPoliciesFor($context->resource, $context->operation);

            return $this->evaluatePolicies($policies, $attributes, $context);
        });
    }

    /**
     * Evaluate a set of policies against attributes.
     *
     * @param  Collection  $policies  The policies to evaluate
     * @param  AttributeCollection  $attributes  The attributes to evaluate against
     * @param  AccessContext  $context  The access context
     *
     * @return EvaluationResult The evaluation result
     */
    private function evaluatePolicies(Collection $policies, AttributeCollection $attributes, AccessContext $context): EvaluationResult
    {
        if ($policies->isEmpty()) {
            return new EvaluationResult(
                granted: false,
                reason: 'No applicable policies found',
                context: [
                    'resource' => $context->resource,
                    'operation' => $context->operation,
                ]
            );
        }

        $matched = $policies
            ->filter(fn (Policy $policy) => $this->evaluatePolicy($policy, $attributes))
            ->values()
            ->all();

        return new EvaluationResult(
            granted: !empty($matched),
            reason: !empty($matched) ? 'Policy conditions met' : 'No matching policies found',
            context: [
                'resource' => $context->resource,
                'operation' => $context->operation,
                'attributes' => $attributes->toArray(),
            ],
            matched: $matched
        );
    }

    /**
     * Evaluate a single policy against attributes.
     *
     * @param  Policy  $policy  The policy to evaluate
     * @param  AttributeCollection  $attributes  The attributes to evaluate against
     *
     * @return bool True if policy conditions are met
     */
    private function evaluatePolicy(Policy $policy, AttributeCollection $attributes): bool
    {
        return $policy->collections->every(
            fn (PolicyCollection $collection) => $this->collectionEvaluator->evaluate($collection, $attributes)
        );
    }
}
