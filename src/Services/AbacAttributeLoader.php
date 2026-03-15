<?php

namespace zennit\ABAC\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
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
        $this->validateConfiguredActorModelClass();

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
     * @return array<string, string>
     */
    private function loadAdditionalActorAttributes(string|int $id): array
    {
        $attributes = AbacActorAdditionalAttribute::where('_id', $id)->get();

        if ($attributes->isEmpty() && ! AbacActorAdditionalAttribute::query()->exists()) {
            Log::warning('ABAC actor additional attributes table is empty.', [
                'event' => 'abac.actor_attributes_empty',
            ]);
        }

        $resolved = [];
        foreach ($attributes as $attribute) {
            $resolved[$attribute->key] = $attribute->value;
        }

        return $resolved;
    }

    /**
     * @throws Exception
     */
    private function validateConfiguredActorModelClass(): void
    {
        $actorModelClass = $this->getActorAdditionalAttributes();

        if (! class_exists($actorModelClass)) {
            throw new Exception(sprintf(
                'Configured ABAC actor model class "%s" does not exist.',
                $actorModelClass
            ));
        }

        if (! is_subclass_of($actorModelClass, Model::class)) {
            throw new Exception(sprintf(
                'Configured ABAC actor model class "%s" must extend %s.',
                $actorModelClass,
                Model::class
            ));
        }
    }
}
