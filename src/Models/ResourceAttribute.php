<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceAttribute extends Model
{
    protected $fillable = [
        'resource',
        'attribute_name',
        'attribute_value',
    ];

    protected $casts = [
        'id' => 'integer',
        'resource' => 'string',
        'attribute_name' => 'string',
        'attribute_value' => 'string',
    ];
}
