<?php

use zennit\ABAC\Services\ZennitAbacService;
use zennit\ABAC\Services\ZennitAbacCacheManager;

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
