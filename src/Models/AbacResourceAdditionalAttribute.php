<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use zennit\ABAC\Models\Concerns\FlushesAbacCache;
use zennit\ABAC\Traits\IntegratesAbacAdditionalAttributes;

/**
 * @property string $_id
 * @property string $model
 * @property string $key
 * @property string $value
 */
class AbacResourceAdditionalAttribute extends Model
{
    use FlushesAbacCache;
    use HasUuids;
    use IntegratesAbacAdditionalAttributes;

    protected $primaryKey = '_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'model',
        '_id',
        'key',
        'value',
    ];

    protected $casts = [
        '_id' => 'string',
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
