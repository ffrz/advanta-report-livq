<?php

namespace App\Policies;

use App\Models\DemoPlot;
use App\Models\User;

class DemoPlotPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DemoPlot $item): bool
    {
        return $this->canAccess($user, $item, 'view');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DemoPlot $item): bool
    {
        return $this->canAccess($user, $item, 'update');
    }

    /**
     * Shared authorization logic.
     */
    protected function canAccess(User $user, DemoPlot $item, string $action): bool
    {
        switch ($user->role) {
            case User::Role_Admin:
                return true;

            case User::Role_BS:
                if ($action === 'update') {
                    // boleh add atau edit jika punya sendiri
                    return !$item->id || $item->user_id === $user->id;
                }

                // boleh lihat jika punya sendiri
                return $item->user_id === $user->id;

            case User::Role_Agronomist:
                // boleh lihat jika punya bawahan sendiri
                return $action === 'view' && $item->user->parent_id === $user->id;

            default:
                return false;
        }
    }
}
