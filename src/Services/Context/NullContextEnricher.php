<?php

namespace zennit\ABAC\Services\Context;

use Illuminate\Http\Request;
use zennit\ABAC\Contracts\ContextEnricher;
use zennit\ABAC\DTO\AccessContext;

class NullContextEnricher implements ContextEnricher
{
    public function enrich(AccessContext $context, Request $request): AccessContext
    {
        return $context;
    }
}
