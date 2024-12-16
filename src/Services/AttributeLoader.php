<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\Contracts\AttributeLoaderInterface;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Models\UserAttribute;

class AttributeLoader implements AttributeLoaderInterface
{
    public function loadAttributes(object $subject, string $resource): AttributeCollection
    {
        $attributes = UserAttribute::where('subject_type', get_class($subject))
            ->where('subject_id', $subject->id)
            ->get()
            ->mapWithKeys(function ($attribute) {
                return [$attribute->attribute_name => $attribute->attribute_value];
            })
            ->all();

        return new AttributeCollection($attributes);
    }
}
