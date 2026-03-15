<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use zennit\ABAC\Models\Concerns\FlushesAbacCache;

class AbacResourceAdditionalAttribute extends Model
{
    use FlushesAbacCache;

    protected $fillable = [
        'model',
        '_id',
        'key',
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'model' => 'string',
        'key' => 'string',
        'value' => 'string',
    ];

    protected static function booted(): void
    {
        self::registerAbacCacheFlushHooks();
    }

    public function resource(): MorphTo
    {
        return $this->morphTo(
            name: 'resource',
            type: 'model',
            id: '_id'
        );
    }
}
