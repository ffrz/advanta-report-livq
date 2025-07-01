<?php

namespace App\Policies;

use App\Models\ActivityPlanDetail;
use App\Models\User;

class ActivityPlanDetailPolicy
{

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ActivityPlanDetail $item): bool
    {
        return $item->parent->user_id === $user->id
            || $user->role === 'admin'
            || (
                $user->role === 'agronomist' &&
                $item->parent->user->parent_id === $user->id
            );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ActivityPlanDetail $item): bool
    {
        return $item->parent->user_id === $user->id
            || $user->role === 'admin'
            || (
                $user->role === 'agronomist' &&
                $item->parent->user->parent_id === $user->id
            );
    }
}
