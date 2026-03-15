<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use zennit\ABAC\Models\Concerns\FlushesAbacCache;

class AbacCheck extends Model
{
    use FlushesAbacCache;
    use HasFactory;

    protected $fillable = [
        'chain_id',
        'operator',
        'key',
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'chain_id' => 'integer',
        'operator' => 'string',
        'key' => 'string',
        'value' => 'string',
    ];

    protected static function booted(): void
    {
        self::registerAbacCacheFlushHooks();
    }
}
