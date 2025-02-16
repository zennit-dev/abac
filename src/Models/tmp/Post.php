<?php

namespace zennit\ABAC\Models\tmp;

use Illuminate\Database\Eloquent\Model;
use zennit\ABAC\Traits\IntegratesAbacAdditionalAttributes;

class Post extends Model
{
    use IntegratesAbacAdditionalAttributes;

    protected $fillable = [
        'title',
        'content',
        'created_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'content' => 'string',
        'created_by' => 'integer',
    ];
}
