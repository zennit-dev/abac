<?php

namespace zennit\ABAC\Models;

use Exception;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use zennit\ABAC\Database\Factories\AbacChainFactory;
use zennit\ABAC\Models\Concerns\FlushesAbacCache;

/**
 * @property string $_id
 * @property string $operator
 * @property string|null $_chain
 * @property string|null $_policy
 */
class AbacChain extends Model
{
    use FlushesAbacCache;

    /** @use HasFactory<AbacChainFactory> */
    use HasFactory;

    use HasUuids;

    protected $primaryKey = '_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'operator',
        '_chain',
        '_policy',
    ];

    protected $casts = [
        '_id' => 'string',
        'operator' => 'string',
        '_chain' => 'string',
        '_policy' => 'string',
    ];

    protected static function booted(): void
    {
        static::creating(function (AbacChain $chain) {
            if ($chain->isDirty('_chain') && $chain->isDirty('_policy')) {
                throw new Exception('You can not set both _chain and _policy at the same time');
            }

            if (! $chain->_chain && ! $chain->_policy) {
                throw new Exception('You must set either _chain or _policy');
            }
        });

        self::registerAbacCacheFlushHooks();
    }

    /**
     * @return BelongsTo<AbacChain, $this>
     */
    public function chain(): BelongsTo
    {
        return $this->belongsTo(AbacChain::class, '_chain');
    }

    /**
     * @return BelongsTo<AbacPolicy, $this>
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(AbacPolicy::class, '_policy');
    }

    /**
     * @return HasMany<AbacCheck, $this>
     */
    public function checks(): HasMany
    {
        return $this->hasMany(AbacCheck::class, '_chain');
    }
}
