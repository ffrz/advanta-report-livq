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
    public function view(User $user, DemoPlotVisit $demoPlotVisit): bool
    {
        return $demoPlotVisit->user_id === $user->id
            || $user->role === 'admin'
            || $user->role === 'agronomist';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DemoPlotVisit $demoPlotVisit): bool
    {
        return $demoPlotVisit->user_id === $user->id
            || $user->role === 'admin';
    }
}
