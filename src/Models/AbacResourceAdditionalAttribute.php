<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use zennit\ABAC\Models\Concerns\FlushesAbacCache;
use zennit\ABAC\Traits\IntegratesAbacAdditionalAttributes;

/**
 * @property int $id
 * @property string $model
 * @property string|null $_id
 * @property string $key
 * @property string $value
 */
class AbacResourceAdditionalAttribute extends Model
{
    use FlushesAbacCache;
    use IntegratesAbacAdditionalAttributes;

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

    /**
     * @return MorphTo<Model, $this>
     */
    public function resource(): MorphTo
    {
        return $this->morphTo(
            name: 'resource',
            type: 'model',
            id: '_id'
        );
    }
}
