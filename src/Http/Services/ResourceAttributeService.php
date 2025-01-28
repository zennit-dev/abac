<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\ResourceAttribute;

readonly class ResourceAttributeService
{
    public function index(): array
    {
        return ResourceAttribute::all()->toArray();
    }

    public function store(array $data): array
    {
        return ResourceAttribute::create($data)->toArray();
    }

    public function show(int $user_attribute): ResourceAttribute
    {
        return ResourceAttribute::findOrFail($user_attribute);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $user_attribute): ResourceAttribute
    {
        $user_attribute = ResourceAttribute::findOrFail($user_attribute);
        $user_attribute->updateOrFail($data);

        return $user_attribute;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $user_attribute): void
    {
        ResourceAttribute::findOrFail($user_attribute)->deleteOrFail();
    }
}
