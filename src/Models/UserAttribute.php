<?php

namespace zennit\ABAC\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserAttribute extends Model
{
    use HasFactory;

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
