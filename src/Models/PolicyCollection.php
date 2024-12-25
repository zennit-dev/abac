<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolicyCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator',
        'policy_id',
    ];

	protected $casts = [
		'id' => 'integer',
		'policy_id' => 'integer',
		'operator' => 'string',
	];

    public function policy(): belongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(PolicyCondition::class);
    }
}
