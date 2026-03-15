<?php

namespace zennit\ABAC\Contracts;

use Illuminate\Http\Request;
use zennit\ABAC\DTO\AccessContext;

interface ContextEnricher
{
    public function enrich(AccessContext $context, Request $request): AccessContext;
}
