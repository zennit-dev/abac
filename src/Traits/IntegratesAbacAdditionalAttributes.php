<?php

namespace zennit\ABAC\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use zennit\ABAC\Models\AbacSubjectAdditionalAttribute;

trait IntegratesAbacAdditionalAttributes
{
    public function additional_attributes(): MorphMany
    {
        return $this->morphMany(
            related: AbacSubjectAdditionalAttribute::class,
            name: 'subject',
            type: 'subject_class',
            id: '_id',
        );
    }
}
