<?php

use zennit\ABAC\Contracts\AbacManager;

if (! function_exists('abacPolicy')) {
    function abacPolicy(): AbacManager
    {
        return app(AbacManager::class);
    }
}
