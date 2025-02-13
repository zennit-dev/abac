<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

class AbacObjectAdditionalAttribute extends Model
{
    use AccessesAbacConfiguration;

    protected $fillable = [
        '_id',
        'key',
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        '_id' => 'integer',
        'key' => 'string',
        'value' => 'string',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
