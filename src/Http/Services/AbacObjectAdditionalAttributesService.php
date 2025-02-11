<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\AbacObjectAdditionalAttributes;

readonly class AbacObjectAdditionalAttributesService
{
    public function index(): array
    {
        return AbacObjectAdditionalAttributes::all()->toArray();
    }

    public function store(array $data): array
    {
        return AbacObjectAdditionalAttributes::create($data)->toArray();
    }

    public function show(int $user_attribute): AbacObjectAdditionalAttributes
    {
        return AbacObjectAdditionalAttributes::findOrFail($user_attribute);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $user_attribute): AbacObjectAdditionalAttributes
    {
        $user_attribute = AbacObjectAdditionalAttributes::findOrFail($user_attribute);
        $user_attribute->updateOrFail($data);

        return $user_attribute;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $user_attribute): void
    {
        AbacObjectAdditionalAttributes::findOrFail($user_attribute)->deleteOrFail();
    }
}
