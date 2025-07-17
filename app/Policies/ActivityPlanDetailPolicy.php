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
        return $this->canAccess($user, $item);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ActivityPlanDetail $item): bool
    {
        return $this->canAccess($user, $item);
    }

    /**
     * Shared authorization logic for view and update.
     */
    protected function canAccess(User $user, ActivityPlanDetail $item): bool
    {
        switch ($user->role) {
            case User::Role_Admin:
                return true;

            case User::Role_BS:
                return !$item->id || $item->parent->user_id === $user->id;

            case User::Role_Agronomist:
                return !$item->id || $item->parent->user->parent_id === $user->id;

            default:
                return false;
        }
    }
}
