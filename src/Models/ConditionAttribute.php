<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConditionAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_condition_id',
        'operator',
        'attribute_name',
        'attribute_value',
    ];

    protected $casts = [
        'id' => 'integer',
        'collection_condition_id' => 'integer',
        'operator' => 'string',
        'attribute_name' => 'string',
        'attribute_value' => 'string',
    ];

    public function condition(): BelongsTo
    {
        return $this->belongsTo(CollectionCondition::class, 'collection_condition_id');
    }
}
