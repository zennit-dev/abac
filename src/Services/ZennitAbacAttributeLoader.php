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
     * Load all attributes required for evaluating an access context.
     *
     * @param AccessContext $context The context containing subject and resource
     * @return AttributeCollection Collection of all relevant attributes
     * @throws InvalidArgumentException If cache operations fail
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

    /**
     * Load attributes associated with a user/subject.
     *
     * @param AccessContext $context The context containing the subject
     * @return array Array of user attributes
     */
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

    /**
     * Load attributes associated with a resource.
     *
     * @param AccessContext $context The context containing the resource
     * @return array Array of resource attributes
     */
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
