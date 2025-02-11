<?php

namespace zennit\ABAC\Http\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class AbacIndexPolicy
{
    use HandlesAuthorization;

    public function viewAny($user, $model)
    {
        return $user->can('read', $model);
    }
}
