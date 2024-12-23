<?php

use zennit\ABAC\Services\AbacService;

if (!function_exists('abac')) {
    function abac(): AbacService
    {
        return app('abac');
    }
}
