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
        'policy_collection_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'operator' => 'string',
        'policy_collection_id' => 'integer',
    ];

    protected static function newFactory(): PolicyConditionFactory
    {
        return PolicyConditionFactory::new();
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(PolicyCollection::class, 'policy_collection_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(PolicyConditionAttribute::class, 'policy_condition_id');
    }
}
