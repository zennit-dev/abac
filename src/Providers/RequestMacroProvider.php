<?php

namespace zennit\ABAC\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use zennit\ABAC\DTO\AccessResult;

class RequestMacroProvider extends ServiceProvider
{
    public function boot(): void
    {
        Request::macro('abac', function (): AccessResult {
            return $this->get('abac');
        });
    }
}
