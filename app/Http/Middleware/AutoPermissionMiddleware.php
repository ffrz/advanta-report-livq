<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class AutoPermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = Route::currentRouteName();

        if (!$user || !$routeName) {
            abort(401, 'Unauthorized or no route name.');
        }

        if ($user->role == User::Role_Admin) {
            return $next($request);
        }

        $permissions = config("roles.{$user->role}", []);

        if (!in_array($routeName, $permissions)) {
            abort(403, "Access to $routeName restricted!");
        }

        return $next($request);
    }
}
