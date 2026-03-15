<?php

use Illuminate\Http\Request;
use zennit\ABAC\DTO\AccessContext;
use zennit\ABAC\DTO\AccessResult;
use zennit\ABAC\Enums\PolicyMethod;
use zennit\ABAC\Facades\Abac;
use zennit\ABAC\Tests\Fixtures\Models\Post;
use zennit\ABAC\Tests\Fixtures\Models\User;

function makeAccessResult(string $userId, string $postId): AccessResult
{
    $user = User::query()->create([
        '_id' => $userId,
        'slug' => $userId,
        'name' => 'Macro Test User',
        'role' => 'member',
    ]);

    Post::query()->create([
        '_id' => $postId,
        'slug' => $postId,
        'title' => 'Macro Test Post',
        'owner_id' => $userId,
    ]);

    $context = new AccessContext(
        method: PolicyMethod::READ,
        resource: Post::query(),
        actor: $user,
    );

    return new AccessResult(
        query: Post::query(),
        reason: null,
        context: $context,
        can: true,
    );
}

it('reads ABAC context from request attributes first', function () {
    Abac::macros();

    $attributeResult = makeAccessResult('u_macro_attr', 'p_macro_attr');
    $inputResult = makeAccessResult('u_macro_input', 'p_macro_input');

    $request = Request::create('/posts', 'GET', ['abac' => $inputResult]);
    $request->attributes->set('abac', $attributeResult);

    expect($request->abac())->toBe($attributeResult);
});

it('falls back to request input when attribute context is missing', function () {
    Abac::macros();

    $inputResult = makeAccessResult('u_macro_fallback', 'p_macro_fallback');
    $request = Request::create('/posts', 'GET', ['abac' => $inputResult]);

    expect($request->abac())->toBe($inputResult);
});
