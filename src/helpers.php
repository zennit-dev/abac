<?php

use zennit\ABAC\Services\AbacCacheManager;
use zennit\ABAC\Services\AbacService;

if (!function_exists('abacPolicy')) {
    function abacPolicy(): AbacService
    {
        return app('zennit.abac.facade');
    }
}

if (!function_exists('abacCache')) {
    function abacCache(): AbacCacheManager
    {
        return app('zennit.abac.cache');
    }
}
