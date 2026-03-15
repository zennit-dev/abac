<?php

namespace zennit\ABAC\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface ResourceResolver
{
    /**
     * @param  array<string, string>  $patterns
     * @return Builder<Model>|null
     */
    public function resolve(Request $request, array $patterns): ?Builder;
}
