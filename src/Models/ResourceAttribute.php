<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource',
        'attribute_name',
        'attribute_value',
    ];
}
