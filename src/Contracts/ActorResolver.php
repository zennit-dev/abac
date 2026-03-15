<?php

namespace zennit\ABAC\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface ActorResolver
{
    public function resolve(Request $request, string $method): Model;
}
