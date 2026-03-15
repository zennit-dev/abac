<?php

use zennit\ABAC\Services\AbacService;
use zennit\ABAC\Tests\Fixtures\Models\Post;

it('builds primary key candidates from model and fallback config', function () {
    config()->set('abac.database.primary_key', 'id');
    config()->set('abac.database.fallback_primary_key', '_id');

    $service = app(AbacService::class);
    $keys = $service->getPrimaryKeyCandidates(new Post);

    expect($keys)->toContain('_id')
        ->and($keys)->toContain('id');
});
