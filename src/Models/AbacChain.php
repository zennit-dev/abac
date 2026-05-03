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
 * @property string|null $chain_id
 * @property string|null $policy_id
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
        'chain_id',
        'policy_id',
    ];

    protected $casts = [
        '_id' => 'string',
        'operator' => 'string',
        'chain_id' => 'string',
        'policy_id' => 'string',
    ];

    protected static function booted(): void
    {
        static::creating(function (AbacChain $chain) {
            if ($chain->isDirty('chain_id') && $chain->isDirty('policy_id')) {
                throw new Exception('You can not set both chain_id and policy_id at the same time');
            }

            if (! $chain->chain_id && ! $chain->policy_id) {
                throw new Exception('You must set either chain_id or policy_id');
            }
        });

        self::registerAbacCacheFlushHooks();
    }

    /**
     * @return BelongsTo<AbacChain, $this>
     */
    public function chain(): BelongsTo
    {
        return $this->belongsTo(AbacChain::class, 'chain_id');
    }

    /**
     * @return HasMany<AbacCheck, $this>
     */
    public function checks(): HasMany
    {
        return $this->hasMany(AbacCheck::class, 'chain_id');
    }
}
