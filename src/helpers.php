<?php

use zennit\ABAC\Contracts\AbacManager;
use zennit\ABAC\Services\AbacService;

if (!function_exists('abacPolicy')) {
    function abacPolicy(): AbacService
    {
        return app(AbacManager::class);
    }
}
