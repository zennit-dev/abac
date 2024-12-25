<?php

use zennit\ABAC\Services\ZennitAbacService;

if (!function_exists('abac')) {
    function abac(): ZennitAbacService
    {
        return app('abac');
    }
}
