<?php

namespace zennit\ABAC\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use zennit\ABAC\Models\AbacSubjectAdditionalAttribute;

trait IntegratesAbacAdditionalAttributes
{
    protected static function bootIntegratesAbacAdditionalAttributes(): void
    {
        static::addGlobalScope(function ($query) {
            $query->with('additional_attributes');
        });

        static::retrieved(function ($model) {
            $model->append();
        });
    }

    public function append(array $attributes = []): static
    {
        $additionalAttributes = $this->additional_attributes
            ->pluck('value', 'key')
            ->toArray();

        return parent::append(array_merge($attributes, $additionalAttributes));
    }

    public function toArray(): array
    {
        $this->append();

        return parent::toArray();
    }

    public function additional_attributes(): MorphMany
    {
        return $this->morphMany(
            related: AbacSubjectAdditionalAttribute::class,
            name: 'subject',
            type: 'subject_class_string',
            id: '_id',
        );
    }
}
