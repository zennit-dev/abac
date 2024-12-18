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
        private ConfigurationService $config
    ) {
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     * @throws InvalidArgumentException
     * @throws UnsupportedOperatorException
     */
    public function evaluate(array $contexts): array
    {
        if ($this->config->getParallelEvaluationEnabled()) {
            return $this->evaluateParallel($contexts);
        }

        return $this->evaluateSequential($contexts);
    }

    /**
     * @throws UnsupportedOperatorException
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    private function evaluateSequential(array $contexts): array
    {
        $results = [];
        $chunks = array_chunk($contexts, $this->config->getBatchChunkSize());

        foreach ($chunks as $chunk) {
            foreach ($chunk as $context) {
                $results[] = $this->abacService->evaluate($context);
            }
        }

        return $results;
    }

    /**
     * @throws \Throwable
     */
    private function evaluateParallel(array $contexts): array
    {
        $jobs = Collection::make($contexts)
            ->chunk($this->config->getBatchChunkSize())
            ->map(function ($chunk) {
                return $chunk->map(fn (AccessContext $context) => new EvaluateAccessJob($context));
            })
            ->flatten();

        $batch = Bus::batch($jobs)
            ->allowFailures()
            ->onQueue($this->config->getAsyncEvents() ? 'abac-evaluations' : null);

        return $batch->dispatch()->toArray();
    }
}
