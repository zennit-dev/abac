<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AbacSubjectAdditionalAttribute extends Model
{
    protected $fillable = [
        'subject_class',
        '_id',
        'key',
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'subject_class' => 'string',
        '_id' => 'integer',
        'key' => 'string',
        'value' => 'string',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo(
            name: 'subject',
            type: 'subject_class',
            id: '_id'
        );
    }
}
