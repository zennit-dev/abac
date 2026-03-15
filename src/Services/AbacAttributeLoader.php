<?php

namespace zennit\ABAC\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use zennit\ABAC\Models\AbacActorAdditionalAttribute;
use zennit\ABAC\Traits\AccessesAbacConfiguration;

readonly class AbacAttributeLoader
{
    use AccessesAbacConfiguration;

    /**
     * Load attributes associated with an actor.
     *
     * @template TActor of Model
     *
     * @param  TActor  $actor  The context containing the actor
     *
     * @returns Model - the $actor as it was + the additional attributes assigned through magic methods
     *
     * @throws Exception
     */
    public function loadAllActorAttributes(Model $actor): Model
    {
        $actorId = $actor->getKey();

        if (is_null($actorId)) {
            throw new Exception('Actor model does not have a resolved primary key value');
        }

        $additions = $this->loadAdditionalActorAttributes($actorId);
        foreach ($additions as $key => $value) {
            $actor->$key = $value;
        }

        return $actor;
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function loadAdditionalActorAttributes(string|int $id): array
    {
        $attributes = AbacActorAdditionalAttribute::where('_id', $id)->get();

        return $attributes->map(fn (AbacActorAdditionalAttribute $attribute) => [$attribute->key, $attribute->value])->toArray();
    }
}
