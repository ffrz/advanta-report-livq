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
        if ($user->role === User::Role_Admin) return true;

        if ($user->role === User::Role_BS) {
            return $item->user_id == $user->id;
        }

        if ($user->role === User::Role_Agronomist) {
            return $item->user->parent_id == $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ActivityPlanDetail $item): bool
    {
        if ($user->role === User::Role_Admin) return true;

        if ($user->role === User::Role_BS) {
            return $item->user_id == $user->id;
        }

        if ($user->role === User::Role_Agronomist) {
            return $item->user->parent_id == $user->id;
        }

        return false;
    }
}
