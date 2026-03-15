<?php

namespace zennit\ABAC\Services\Resolution;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use RuntimeException;
use zennit\ABAC\Contracts\ActorResolver;

class DefaultActorResolver implements ActorResolver
{
    public function resolve(Request $request, string $method): Model
    {
        if (! is_callable([$request, $method])) {
            throw new RuntimeException("Actor method '$method' is not callable on request");
        }

        $actor = $request->$method();

        if (is_null($actor)) {
            throw new RuntimeException("Actor method '$method' returned null");
        }

        return $actor;
    }
}
