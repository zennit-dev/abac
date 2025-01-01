<?php

use zennit\ABAC\Services\ZennitAbacCacheManager;
use zennit\ABAC\Services\ZennitAbacService;

if (!function_exists('abacPolicy')) {
    function abacPolicy(): ZennitAbacService
    {
        return app('zennit.abac.facade');
    }
}

if (!function_exists('abacCache')) {
    function abacCache(): ZennitAbacCacheManager
    {
        return app('zennit.abac.cache');
    }
}
