<?php

namespace zennit\ABAC\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbacChain extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator',
        'chain_id',
        'policy_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'operator' => 'string',
        'chain_id' => 'integer',
        'policy_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (AbacChain $chain) {
            if ($chain->isDirty('chain_id') && $chain->isDirty('policy_id')) {
                throw new Exception('You can not set both chain_id and policy_id at the same time');
            }

            if (!$chain->chain_id && !$chain->policy_id) {
                throw new Exception('You must set either chain_id or policy_id');
            }
        });
    }

    public function chain(): BelongsTo
    {
        return $this->belongsTo(AbacChain::class, 'chain_id');
    }

    public function checks(): HasMany
    {
        return $this->hasMany(AbacCheck::class, 'chain_id');
    }
}
