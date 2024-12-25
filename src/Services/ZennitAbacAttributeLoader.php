<?php

namespace zennit\ABAC\Services;

use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Models\ResourceAttribute;
use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

readonly class ZennitAbacAttributeLoader
{
    use ZennitAbacHasConfigurations;

    public function __construct(
        private ZennitAbacCacheManager $cache,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function loadForContext(AccessContext $context): AttributeCollection
    {
        // Cache user attributes
        $subjectAttributes = $this->cache->rememberUserAttributes(
            $context->subject->id,
            get_class($context->subject),
            fn () => $this->loadUserAttributes($context)
        );

        // Cache resource attributes
        $resourceAttributes = $this->cache->rememberResourceAttributes(
            $context->resource,
            fn () => $this->loadResourceAttributes($context)
        );

        return new AttributeCollection([...$subjectAttributes, ...$resourceAttributes]);
    }

    private function loadUserAttributes(AccessContext $context): array
    {
        $attributes = [];
        $subjectAttributes = UserAttribute::query()
            ->where('subject_type', get_class($context->subject))
            ->where('subject_id', $context->subject->id)
            ->get();

        foreach ($subjectAttributes as $attribute) {
            $attributes["subject.$attribute->attribute_name"] = $attribute->attribute_value;
        }

        return $attributes;
    }

    private function loadResourceAttributes(AccessContext $context): array
    {
        $attributes = [];
        $resourceAttributes = ResourceAttribute::query()
            ->where('resource', $context->resource)
            ->get();

        foreach ($resourceAttributes as $attribute) {
            $attributes["resource.$attribute->attribute_name"] = $attribute->attribute_value;
        }

        return $attributes;
    }
}
