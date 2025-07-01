<?php

namespace App\Policies;

use App\Models\DemoPlot;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DemoPlotPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DemoPlot $item): bool
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
    public function update(User $user, DemoPlot $item): bool
    {
        return $user->role === 'admin' || $item->user_id === $user->id;
    }
}
