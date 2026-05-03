<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use zennit\ABAC\Database\Factories\AbacCheckFactory;
use zennit\ABAC\Models\Concerns\FlushesAbacCache;

/**
 * @property string $_id
 * @property string $chain_id
 * @property string $operator
 * @property string $key
 * @property string $value
 */
class AbacCheck extends Model
{
    use FlushesAbacCache;

    /** @use HasFactory<AbacCheckFactory> */
    use HasFactory;

    use HasUuids;

    protected $primaryKey = '_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'chain_id',
        'operator',
        'key',
        'value',
    ];

    protected $casts = [
        '_id' => 'string',
        'chain_id' => 'string',
        'operator' => 'string',
        'key' => 'string',
        'value' => 'string',
    ];

    protected static function booted(): void
    {
        self::registerAbacCacheFlushHooks();
    }
}
