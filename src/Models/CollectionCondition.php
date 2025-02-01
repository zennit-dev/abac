<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use zennit\ABAC\Database\Factories\CollectionConditionFactory;

class CollectionCondition extends Model
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

    protected static function newFactory(): CollectionConditionFactory
    {
        return CollectionConditionFactory::new();
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(PolicyCollection::class, 'policy_collection_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ConditionAttribute::class, 'collection_condition_id');
    }
}
