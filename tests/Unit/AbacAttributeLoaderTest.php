<?php

use Illuminate\Support\Facades\Log;
use zennit\ABAC\Services\AbacAttributeLoader;
use zennit\ABAC\Tests\Fixtures\Models\User;

it('throws when configured actor model class does not exist', function () {
    config()->set('abac.database.actor_additional_attributes', 'App\\Models\\MissingActor');

    $user = User::query()->create([
        '_id' => 'u_invalid_actor_model',
        'slug' => 'invalid-actor-model-user',
        'name' => 'Invalid Actor Model User',
        'role' => 'member',
    ]);

    $loader = app(AbacAttributeLoader::class);

    expect(fn () => $loader->loadAllActorAttributes($user))
        ->toThrow(Exception::class, 'Configured ABAC actor model class "App\\Models\\MissingActor" does not exist.');
});

it('logs a warning when actor additional attributes table is empty', function () {
    Log::spy();

    $user = User::query()->create([
        '_id' => 'u_empty_attrs',
        'slug' => 'empty-attrs-user',
        'name' => 'Empty Attrs User',
        'role' => 'member',
    ]);

    $loader = app(AbacAttributeLoader::class);
    $loader->loadAllActorAttributes($user);

    Log::shouldHaveReceived('warning')->once()->withArgs(
        fn (string $message, array $context): bool => $message === 'ABAC actor additional attributes table is empty.'
            && ($context['event'] ?? null) === 'abac.actor_attributes_empty'
    );
});
