<?php

namespace zennit\ABAC\Services\CacheKey;

use zennit\ABAC\Contracts\CacheKeyStrategy;
use zennit\ABAC\DTO\AccessContext;

class DefaultCacheKeyStrategy implements CacheKeyStrategy
{
    public function make(AccessContext $context, bool $includeContext): string
    {
        $resource = $context->resource;
        $resourceModel = $resource->getModel();
        $actor = $context->actor;

        $parts = [
            $context->method->value,
            get_class($resourceModel),
            $resource->toSql(),
            (string) json_encode($resource->getBindings()),
        ];

        if ($includeContext) {
            $parts[] = $actor::class;
            $parts[] = (string) $actor->getKey();
            $parts[] = $this->normalizeEnvironment($context->environment);
        }

        return sha1(implode('|', $parts));
    }

    /**
     * @param  array<string, mixed>  $environment
     */
    private function normalizeEnvironment(array $environment): string
    {
        ksort($environment);

        return (string) json_encode($environment);
    }
}
