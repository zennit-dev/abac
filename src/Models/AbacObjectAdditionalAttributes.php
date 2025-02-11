<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use zennit\ABAC\Traits\AbacHasConfigurations;

class AbacObjectAdditionalAttributes extends Model
{
    use AbacHasConfigurations;

    protected $fillable = [
        'object_id',
        'attribute_name',
        'attribute_value',
    ];

    protected $casts = [
        'id' => 'integer',
        'object_id' => 'integer',
        'attribute_name' => 'string',
        'attribute_value' => 'string',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
