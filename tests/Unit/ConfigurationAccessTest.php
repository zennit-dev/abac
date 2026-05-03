<?php

use zennit\ABAC\Services\AbacService;

it('falls back to id when no primary key is configured', function () {
    config()->offsetUnset('abac.database.primary_key');

    $service = app(AbacService::class);
    expect($service->getPrimaryKey())->toBe('id');
});

it('uses the configured primary key when present', function () {
    config()->set('abac.database.primary_key', '_id');

    $service = app(AbacService::class);

    expect($service->getPrimaryKey())->toBe('_id');
});
