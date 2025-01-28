<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\UserAttribute;

readonly class UserAttributeService
{
    public function index(): array
    {
        return UserAttribute::all()->toArray();
    }

    public function store(array $data): array
    {
        return UserAttribute::create($data)->toArray();
    }

    public function show(int $user_attribute): UserAttribute
    {
        return UserAttribute::findOrFail($user_attribute);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $user_attribute): UserAttribute
    {
        $user_attribute = UserAttribute::findOrFail($user_attribute);
        $user_attribute->updateOrFail($data);

        return $user_attribute;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $user_attribute): void
    {
        UserAttribute::findOrFail($user_attribute)->deleteOrFail();
    }
}
