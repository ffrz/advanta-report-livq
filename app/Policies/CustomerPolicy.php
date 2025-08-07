<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $item): bool
    {
        return $this->canAccess($user, $item, 'view');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $item): bool
    {
        return $this->canAccess($user, $item, 'update');
    }

    /**
     * Shared authorization logic.
     */
    protected function canAccess(User $user, Customer $item, string $action): bool
    {
        switch ($user->role) {
            case User::Role_Admin:
                return true;

            case User::Role_BS:
                if ($action === 'update') {
                    // boleh add atau edit jika punya sendiri
                    return !$item->id || $item->assigned_user_id === $user->id;
                }

                // boleh lihat jika punya sendiri
                return $item->assigned_user_id === $user->id;

            case User::Role_Agronomist:
                // boleh lihat jika punya bawahan sendiri
                return $action === 'view' && $item->user->parent_id === $user->id;

            default:
                return false;
        }
    }
}
