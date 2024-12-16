<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
		'name',
		'permission_id',
    ];

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(PolicyCondition::class);
    }
}
