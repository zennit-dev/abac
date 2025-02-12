<?php

namespace zennit\ABAC\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use zennit\ABAC\Models\AbacObjectAdditionalAttribute;
use zennit\ABAC\Models\AbacSubjectAdditionalAttribute;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

readonly class AbacAttributeLoader
{
    use AccessesAbacConfiguration;

    /**
     * Load attributes associated with a user/subject.
     *
     * @template TObject of Model
     *
     * @param  TObject  $object  The context containing the subject
     *
     * @returns Model - the $object as it was + the additional attributes assigned through magic methods
     *
     * @throws Exception
     */
    public function loadAllObjectAttributes(Model $object): Model
    {
        if (!isset($object->id)) {
            throw new Exception('Object Model does not have an ID field');
        }

        $additions = $this->loadAdditionalObjectAttributes($object->id);
        foreach ($additions as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }

    private function loadAdditionalObjectAttributes(int $id): array
    {
        $attributes = AbacObjectAdditionalAttribute::where('object_id', $id)->get();

        return $attributes->map(fn (AbacObjectAdditionalAttribute $attribute) => [$attribute->attribute_name, $attribute->attribute_value])->toArray();
    }

    /**
     * Load attributes associated with a resource.
     *
     * @param string $model_class_string
     * @param string|int $id
     *
     * @return Collection additional defined attributes
     */
    public function loadAdditionalSubjectAttributes(string $model_class_string, string|int $id): Collection
    {
        return AbacSubjectAdditionalAttribute::where('subject', $model_class_string)
            ->where('subject_id', $id)->get();

    }
}
