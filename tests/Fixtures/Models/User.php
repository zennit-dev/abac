<?php

namespace zennit\ABAC\Tests\Fixtures\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $primaryKey = '_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        '_id',
        'slug',
        'name',
        'email',
        'password',
        'role',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
