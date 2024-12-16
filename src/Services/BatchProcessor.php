<?php

namespace zennit\ABAC\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Jobs\EvaluateAccessJob;

readonly class BatchProcessor
{
    public function __construct(
        private AbacService $abacService,
        private array $config
    ) {}

    /**
     * @throws UnsupportedOperatorException
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public function evaluate(array $contexts): array
    {
        $results = [];
        $chunks = array_chunk($contexts, $this->config['batch_size'] ?? 1000);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $context) {
                $results[] = $this->abacService->evaluate($context);
            }
        }

        return $results;
    }

    /**
     * @throws Throwable
     */
    public function evaluateParallel(array $contexts): array
    {
        $jobs = Collection::make($contexts)
            ->map(fn (AccessContext $context) => new EvaluateAccessJob($context));

        return Bus::batch($jobs)
            ->allowFailures()
            ->dispatch()
            ->toArray();
    }
}
