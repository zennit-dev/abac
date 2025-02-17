<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AbacSubjectAdditionalAttribute extends Model
{
    protected $fillable = [
        'model',
        '_id',
        'key',
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'model' => 'string',
        '_id' => 'integer',
        'key' => 'string',
        'value' => 'string',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo(
            name: 'subject',
            type: 'model',
            id: '_id'
        );
    }
}
