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
    public function update(User $user, DemoPlotVisit $item): bool
    {
        return $item->user_id === $user->id
            || $user->role === 'admin';
    }
}
