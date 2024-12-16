<?php

namespace zennit\ABAC\Services;

use Illuminate\Support\Facades\Config;
use zennit\ABAC\Contracts\AttributeLoaderInterface;
use zennit\ABAC\DTO\AttributeCollection;
use zennit\ABAC\Models\ResourceAttribute;

class AttributeLoader implements AttributeLoaderInterface
{
    public function loadAttributes(object $subject, string $resource): AttributeCollection
    {
        $subjectAttrs = $this->loadSubjectAttributes($subject);
        $resourceAttrs = $this->loadResourceAttributes($resource);

        return new AttributeCollection(array_merge($subjectAttrs, $resourceAttrs));
    }

    private function loadSubjectAttributes(object $subject): array
    {
        $config = Config::get('abac.tables.user_attributes');
        $model = Config::get('abac.models.user_attribute');

        return $model::query()
            ->where($config['subject_type_column'], get_class($subject))
            ->where($config['subject_id_column'], $subject->id)
            ->get()
            ->mapWithKeys(function ($attribute) {
                return [$attribute->attribute_name => $attribute->attribute_value];
            })
            ->all();
    }

    private function loadResourceAttributes(string $resource): array
    {
        return ResourceAttribute::where('resource', $resource)
            ->get()
            ->mapWithKeys(function ($attribute) {
                return [$attribute->attribute_name => $attribute->attribute_value];
            })
            ->all();
    }
}
