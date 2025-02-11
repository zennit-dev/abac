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
        'context_accessor',
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'chain_id' => 'integer',
        'operator' => 'string',
        'context_accessor' => 'string',
        'value' => 'string',
    ];
}
