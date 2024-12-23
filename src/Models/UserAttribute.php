<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use zennit\ABAC\Traits\HasConfigurations;

class UserAttribute extends Model
{
    use HasConfigurations;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'attribute_name',
        'attribute_value',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
