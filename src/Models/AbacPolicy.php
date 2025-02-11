<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbacPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource',
        'method',
    ];

    protected $casts = [
        'id' => 'integer',
        'resource' => 'string',
        'method' => 'string',
    ];

    public function chains(): HasMany
    {
        return $this->hasMany(AbacChain::class, 'policy_id');
    }
}
