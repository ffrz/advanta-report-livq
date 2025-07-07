<?php

namespace App\Policies;

use App\Models\DemoPlotVisit;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DemoPlotVisitPolicy
{

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DemoPlotVisit $item): bool
    {
        if ($user->role === User::Role_Admin) return true;

        if ($user->role === User::Role_BS) {
            return $item->user_id === $user->id;
        }

        if ($user->role === User::Role_Agronomist) {
            return $item->user->parent_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DemoPlotVisit $item): bool
    {
        if ($user->role === User::Role_Admin) return true;

        if ($user->role === User::Role_BS) {
            return $item->id ? $item->user_id === $user->id : true;
        }

        return false;
    }
}
