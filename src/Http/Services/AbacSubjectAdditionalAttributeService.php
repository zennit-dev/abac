<?php

namespace zennit\ABAC\Http\Services;

use Throwable;
use zennit\ABAC\Models\AbacSubjectAdditionalAttribute;

readonly class AbacSubjectAdditionalAttributeService
{
    public function index(): array
    {
        return AbacSubjectAdditionalAttribute::all()->toArray();
    }

    public function store(array $data): array
    {
        return AbacSubjectAdditionalAttribute::create($data)->toArray();
    }

    public function show(int $user_attribute): AbacSubjectAdditionalAttribute
    {
        return AbacSubjectAdditionalAttribute::findOrFail($user_attribute);
    }

    /**
     * @throws Throwable
     */
    public function update(array $data, int $user_attribute): AbacSubjectAdditionalAttribute
    {
        $user_attribute = AbacSubjectAdditionalAttribute::findOrFail($user_attribute);
        $user_attribute->updateOrFail($data);

        return $user_attribute;
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $user_attribute): void
    {
        AbacSubjectAdditionalAttribute::findOrFail($user_attribute)->deleteOrFail();
    }
}
