<?php

namespace zennit\ABAC\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\Exceptions\UnsupportedOperatorException;
use zennit\ABAC\Exceptions\ValidationException;
use zennit\ABAC\Services\AbacService;

class BatchEvaluateAccessJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  AccessContext[]  $contexts
     */
    public function __construct(
        private readonly array $contexts,
        private readonly string $callbackEvent
    ) {
    }

    /**
     * @throws UnsupportedOperatorException
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public function handle(AbacService $abacService, Dispatcher $events): void
    {
        $results = [];
        foreach ($this->contexts as $context) {
            $results[] = $abacService->evaluate($context);
        }

        $events->dispatch($this->callbackEvent, $results);
    }
}
