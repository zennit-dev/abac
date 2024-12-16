<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyConditionAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_condition_id',
        'attribute_name',
        'attribute_value',
    ];

    public function condition(): BelongsTo
    {
        return $this->belongsTo(PolicyCondition::class);
    }
}
