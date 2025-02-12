<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;

class AbacSubjectAdditionalAttribute extends Model
{
    protected $fillable = [
        'subject_class_string',
        '_id',
        'key',
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'subject_class_string' => 'string',
        '_id' => 'integer',
        'key' => 'string',
        'value' => 'string',
    ];
}
