<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Models\ResourceAttribute;
use zennit\ABAC\Models\UserAttribute;
use zennit\ABAC\Traits\HasConfigurations;

readonly class AttributeLoader
{
    use HasConfigurations;

    public function loadForContext(AccessContext $context): AttributeCollection
    {
        $attributes = [];

        // Load subject attributes using polymorphic relationship
        $subjectAttributes = UserAttribute::query()
            ->where('subject_type', get_class($context->subject))
            ->where('subject_id', $context->subject->id)
            ->get();

        foreach ($subjectAttributes as $attribute) {
            $attributes["subject.$attribute->attribute_name"] = $attribute->attribute_value;
        }

        // Load resource attributes
        $resourceAttributes = ResourceAttribute::query()
            ->where('resource', $context->resource)
            ->get();

        foreach ($resourceAttributes as $attribute) {
            $attributes["resource.$attribute->attribute_name"] = $attribute->attribute_value;
        }

        return new AttributeCollection($attributes);
    }
}
