<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbacCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'chain_id',
        'operator',
        'key',
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'chain_id' => 'integer',
        'operator' => 'string',
        'key' => 'string',
        'value' => 'string',
    ];
}
