<?php

namespace zennit\ABAC\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use zennit\ABAC\Models\AbacResourceAdditionalAttribute;

trait IntegratesAbacAdditionalAttributes
{
    public function additionalAttributes(): MorphMany
    {
        return $this->morphMany(
            related: AbacResourceAdditionalAttribute::class,
            name: 'resource',
            type: 'model',
            id: '_id',
        );
    }
}
