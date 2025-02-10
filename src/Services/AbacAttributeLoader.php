<?php

namespace zennit\ABAC\Services;

use Psr\SimpleCache\InvalidArgumentException;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Models\ResourceAttribute;
use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Traits\AbacHasConfigurations;

readonly class AbacAttributeLoader
{
    use AbacHasConfigurations;

    public function __construct(
        private AbacCacheManager $cache,
    ) {
    }

    /**
     * Load all attributes required for evaluating an access context.
     *
     * @param AccessContext $context The context containing subject and resource
     *
     * @throws InvalidArgumentException If cache operations fail
     * @return AttributeCollection Collection of all relevant attributes
     */
    public function loadForContext(AccessContext $context): AttributeCollection
    {
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
     *
     * @return array Array of user attributes
     */
    private function loadUserAttributes(AccessContext $context): array
    {
        $attributes = [];

        $subjectType = get_class($context->subject);
        $subjectId = $context->subject->id;

        $subjectAttributes = UserAttribute::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
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
     *
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
