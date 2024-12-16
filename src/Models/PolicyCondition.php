<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use zennit\ABAC\Database\Factories\PolicyConditionFactory;

class PolicyCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator',
        'policy_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'operator' => 'string',
        'policy_id' => 'integer',
    ];

    protected static function newFactory(): PolicyConditionFactory
    {
        return PolicyConditionFactory::new();
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(PolicyConditionAttribute::class, 'policy_condition_id');
    }
}
