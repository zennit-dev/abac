<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use zennit\ABAC\Traits\ZennitAbacHasConfigurations;

class UserAttribute extends Model
{
    use ZennitAbacHasConfigurations;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'attribute_name',
        'attribute_value',
    ];

    protected $casts = [
        'id' => 'integer',
        'subject_type' => 'string',
        'subject_id' => 'integer',
        'attribute_name' => 'string',
        'attribute_value' => 'string',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
