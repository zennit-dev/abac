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
        return $this->applyWithLinkMethod(
            $query,
            $chain,
            $context,
            'where'
        );
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     *
     * @throws Exception
     */
    private function applyWithLinkMethod(Builder $query, AbacChain $chain, AccessContext $context, string $linkMethod): Builder
    {
        $relatedChains = AbacChain::where('chain_id', $chain->id)->get();
        $relatedChecks = AbacCheck::where('chain_id', $chain->id)->get();
        $childLinkMethod = $this->resolveLinkMethod($chain->operator);

        return $query->{$linkMethod}(function (Builder $subQuery) use ($relatedChains, $relatedChecks, $context, $childLinkMethod): void {
            foreach ($relatedChains as $nestedChain) {
                $this->applyWithLinkMethod($subQuery, $nestedChain, $context, $childLinkMethod);
            }

            foreach ($relatedChecks as $check) {
                if ($childLinkMethod === 'orWhere') {
                    $subQuery->orWhere(function (Builder $checkQuery) use ($check, $context): void {
                        $this->checkEvaluator->apply($checkQuery, $check, $context);
                    });

                    continue;
                }

                $this->checkEvaluator->apply($subQuery, $check, $context);
            }
        });
    }

    private function resolveLinkMethod(string $operator): string
    {
        return match ($operator) {
            LogicalOperators::OR->value => 'orWhere',
            default => 'where',
        };
    }
}
