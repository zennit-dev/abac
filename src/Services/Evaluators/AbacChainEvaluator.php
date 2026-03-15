<?php

namespace zennit\ABAC\Services\Evaluators;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Enums\Operators\LogicalOperators;
use zennit\ABAC\Models\AbacChain;
use zennit\ABAC\Models\AbacCheck;

readonly class AbacChainEvaluator
{
    public function __construct(
        private AbacCheckEvaluator $checkEvaluator,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     *
     * @throws Exception
     */
    public function apply(Builder $query, AbacChain $chain, AccessContext $context): Builder
    {
        $relatedChains = AbacChain::where('chain_id', $chain->id)->get();
        $relatedChecks = AbacCheck::where('chain_id', $chain->id)->get();

        $method = match ($chain->operator) {
            LogicalOperators::OR->value => 'orWhere',
            default => 'where'
        };

        return $query->{$method}(
            function (Builder $subQuery) use ($relatedChains, $relatedChecks, $context): void {
                foreach ($relatedChains as $nestedChain) {
                    $this->apply($subQuery, $nestedChain, $context);
                }

                foreach ($relatedChecks as $check) {
                    $this->checkEvaluator->apply($subQuery, $check, $context);
                }
            }
        );
    }
}
