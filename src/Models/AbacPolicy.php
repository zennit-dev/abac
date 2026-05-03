<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use zennit\ABAC\Database\Factories\AbacPolicyFactory;
use zennit\ABAC\Models\Concerns\FlushesAbacCache;

/**
 * @property string $_id
 * @property string $resource
 * @property string $method
 *
 * @use HasFactory<AbacPolicyFactory>
 */
class AbacPolicy extends Model
{
    use FlushesAbacCache;
    /** @use HasFactory<AbacPolicyFactory> */
    use HasFactory;

    use HasUuids;

    protected $primaryKey = '_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'resource',
        'method',
    ];

    protected $casts = [
        '_id' => 'string',
        'resource' => 'string',
        'method' => 'string',
    ];

    protected static function booted(): void
    {
        self::registerAbacCacheFlushHooks();
    }

    /**
     * @return HasMany<AbacChain, $this>
     */
    public function chains(): HasMany
    {
        return $this->hasMany(AbacChain::class, 'policy_id');
    }
}
