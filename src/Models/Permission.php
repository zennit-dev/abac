<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource',
        'operation',
    ];

    protected $casts = [
        'id' => 'integer',
        'resource' => 'string',
        'operation' => 'string',
    ];

    public function policies(): HasMany
    {
        return $this->hasMany(Policy::class, 'permission_id');
    }
}
