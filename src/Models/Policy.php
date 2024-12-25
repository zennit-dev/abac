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

    protected $casts = [
        'id' => 'integer',
        'permission_id' => 'integer',
        'name' => 'string',
    ];

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    public function collections(): HasMany
    {
        return $this->hasMany(PolicyCollection::class, 'policy_id');
    }
}
