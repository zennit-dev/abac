<?php

namespace zennit\ABAC\Jobs;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\PolicyEvaluationResult;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Services\ConfigurationService;

class EvaluateAccessJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly AccessContext $context
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws UnsupportedOperatorException
     * @throws ValidationException
     */
    public function handle(AbacService $abacService, ConfigurationService $config): PolicyEvaluationResult
    {
        if ($config->getPerformanceLoggingEnabled()) {
            $this->onQueue($config->getAsyncEvents() ? 'abac-evaluations' : null);
        }

        return $abacService->evaluate($this->context);
    }

    public function tags(): array
    {
        return [
            'abac',
            'evaluation',
            "resource:{$this->context->resource}",
            "operation:{$this->context->operation}",
        ];
    }

    public function retryUntil(): DateTime
    {
        return now()->addMinutes(5);
    }
}
