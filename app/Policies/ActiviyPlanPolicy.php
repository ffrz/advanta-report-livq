<?php

namespace App\Policies;

use App\Models\ActivityPlan;
use App\Models\User;

class ActiviyPlanPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ActivityPlan $item): bool
    {
        return $item->user_id === $user->id
            || $user->role === 'admin'
            || (
                $user->role === 'agronomist' &&
                $item->user->parent_id === $user->id
            );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ActivityPlan $item): bool
    {
        return $user->role === 'admin' || $item->user_id === $user->id;
    }
}
