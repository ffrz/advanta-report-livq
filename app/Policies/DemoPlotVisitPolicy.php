<?php

namespace App\Policies;

use App\Models\DemoPlotVisit;
use App\Models\User;

class DemoPlotVisitPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DemoPlotVisit $item): bool
    {
        return $this->canAccess($user, $item, 'view');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DemoPlotVisit $item): bool
    {
        return $this->canAccess($user, $item, 'update');
    }

    /**
     * Shared authorization logic.
     */
    protected function canAccess(User $user, DemoPlotVisit $item, string $action): bool
    {
        switch ($user->role) {
            case User::Role_Admin:
                return true;

            case User::Role_BS:
                if ($action === 'update') {
                    return !$item->id || $item->user_id === $user->id;
                }
                return $item->user_id === $user->id;

            case User::Role_Agronomist:
                return $action === 'view' && $item->user->parent_id === $user->id;

            default:
                return false;
        }
    }
}
