<?php

namespace zennit\ABAC\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $primaryKey = '_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        '_id',
        'slug',
        'title',
        'owner_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
