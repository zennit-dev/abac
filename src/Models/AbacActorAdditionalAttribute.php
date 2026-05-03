<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use zennit\ABAC\Models\Concerns\FlushesAbacCache;

/**
 * @property string $_id
 * @property string $key
 * @property string $value
 */
class AbacActorAdditionalAttribute extends Model
{
    use FlushesAbacCache;
    use HasUuids;

    protected $primaryKey = '_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        '_id',
        'key',
        'value',
    ];

    protected $casts = [
        '_id' => 'string',
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
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
