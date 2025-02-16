<?php

namespace zennit\ABAC\Models\tmp;

use Illuminate\Database\Eloquent\Model;
use zennit\ABAC\Traits\IntegratesAbacAdditionalAttributes;

class User extends Model
{
    use IntegratesAbacAdditionalAttributes;

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
    ];
}
