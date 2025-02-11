<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;

class AbacSubjectAdditionalAttribute extends Model
{
    protected $fillable = [
        'subject',
        'subject_id',
        'attribute_name',
        'attribute_value',
    ];

    protected $casts = [
        'id' => 'integer',
        'subject' => 'string',
        'subject_id' => 'integer',
        'attribute_name' => 'string',
        'attribute_value' => 'string',
    ];
}
