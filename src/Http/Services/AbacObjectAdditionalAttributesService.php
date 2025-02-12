<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\AbacObjectAdditionalAttribute;

readonly class AbacObjectAdditionalAttributesService
{
    public function index(): array
    {
        return AbacObjectAdditionalAttribute::all()->toArray();
    }

    public function store(array $data): array
    {
        return AbacObjectAdditionalAttribute::create($data)->toArray();
    }

    public function show(int $user_attribute): AbacObjectAdditionalAttribute
    {
        return AbacObjectAdditionalAttribute::findOrFail($user_attribute);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $user_attribute): AbacObjectAdditionalAttribute
    {
        $user_attribute = AbacObjectAdditionalAttribute::findOrFail($user_attribute);
        $user_attribute->updateOrFail($data);

        return $user_attribute;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $user_attribute): void
    {
        AbacObjectAdditionalAttribute::findOrFail($user_attribute)->deleteOrFail();
    }
}
