<?php

namespace zennit\ABAC\Services;

use zennit\ABAC\Models\AbacObjectAdditionalAttributes;
use zennit\ABAC\Models\AbacSubjectAdditionalAttribute;
use zennit\ABAC\Traits\AbacHasConfigurations;

readonly class AbacAttributeLoader
{
    use AbacHasConfigurations;

    /**
     * Load attributes associated with a user/subject.
     *
     * @param object $object The context containing the subject
     *
     * @return array Array of user attributes
     */
    public function loadAllObjectAttributes(object $object): array
    {
        $attributes = $object->toArray();
        $additionalAttributes = $this->loadAdditionalObjectAttributes($object->id);

        return [...$attributes, ...$additionalAttributes];
    }

    private function loadAdditionalObjectAttributes(int $subjectId): array
    {
        $subjectAttributes = AbacObjectAdditionalAttributes::query()
            ->where('subject_id', $subjectId)
            ->get();

        $attributes = [];
        foreach ($subjectAttributes as $attribute) {
            $attributes["subject.$attribute->attribute_name"] = $attribute->attribute_value;
        }

        return $attributes;
    }

    /**
     * Load attributes associated with a resource.
     *
     * @param object|null $subject
     *
     * @return array Array of resource attributes
     */
    public function loadAllSubjectAttributes(?object $subject): array
    {
        $attributes = $subject?->toArray() ?? [];
        $additionalAttributes = $this->loadAdditionalSubjectAttributes($subject);

        return [...$attributes, ...$additionalAttributes];
    }

    private function loadAdditionalSubjectAttributes(object $subject): array
    {
        $additionalAttributes = AbacSubjectAdditionalAttribute::query()
            ->where('subject', get_class($subject))
            ->where(function ($query) use ($subject) {
                $query->where('subject_id', $subject->id)
                    ->orWhere('subject_id', null);
            })
            ->get();

        $attributes = [];
        foreach ($additionalAttributes as $attribute) {
            $attributes["resource.$attribute->attribute_name"] = $attribute->attribute_value;
        }

        return $attributes;
    }
}
