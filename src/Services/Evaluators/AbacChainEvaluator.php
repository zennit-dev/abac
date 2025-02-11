<?php

namespace zennit\ABAC\Services\Evaluators;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;
use zennit\ABAC\Strategies\OperatorFactory;
use zennit\ABAC\Traits\AbacHasConfigurations;

readonly class AbacChainEvaluator
{
    use AbacHasConfigurations;

    public function __construct(
        private OperatorFactory $operatorFactory,
        private AbacCheckEvaluator $checkEvaluator,
    ) {
    }

    /**
     * Evaluate a policy collection against attributes.
     *
     * @param AbacChain $link The attributes to evaluate against
     * @param AccessContext $context The access context for contextual evaluation
     *
     * @throws UnsupportedOperatorException If an operator is not supported
     * @return bool True if collection conditions are met
     */
    public function evaluate(
        AbacChain $link,
        AccessContext $context
    ): bool {
        $operator = $this->operatorFactory->create($link->operator);

        $related_chains = array_map(function ($chain) {
            return ['type' => 'chain'] + $chain;
        }, AbacChain::where('chain_id', $link->id)->toArray());

        $related_checks = array_map(function ($check) {
            return ['type' => 'check'] + $check;
        }, AbacCheck::where('chain_id', $link->id)->toArray());

        $related = [...$related_checks, ...$related_chains];

        $results = array_map(function ($item) use ($context) {
            return $item['type'] === 'chain'
                ? $this->evaluate($item, $context)
                : $this->checkEvaluator->evaluate($item, $context);
        }, $related);

        return $operator->evaluate(
            values: $results,
            context: $context
        );
    }
}
