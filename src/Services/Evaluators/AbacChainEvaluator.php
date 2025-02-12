<?php

namespace zennit\ABAC\Services\Evaluators;

use Illuminate\Database\Eloquent\Builder;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;

readonly class AbacChainEvaluator
{
    public function __construct(
        private AbacCheckEvaluator $checkEvaluator,
    ) {}

    public function evaluate(Builder $query, AbacChain $chain, AccessContext $context): Builder
    {
        // Get all related chains and checks
        $related_chains = AbacChain::where('chain_id', $chain->id)->get();
        $related_checks = AbacCheck::where('chain_id', $chain->id)->get();

        // Determine the method based on operator
        $method = match ($chain->operator) {
            'and' => 'where',
            'or' => 'orWhere',
            default => 'where'
        };

        // Apply the constraints using a closure to maintain proper grouping
        return $query->{$method}(function ($subQuery) use ($related_chains, $related_checks, $context) {
            // Apply nested chains
            foreach ($related_chains as $nested_chain) {
                $this->evaluate($subQuery, $nested_chain, $context);
            }

            // Apply checks
            foreach ($related_checks as $check) {
                $this->checkEvaluator->evaluate($subQuery, $check, $context);
            }
        });
    }
}
