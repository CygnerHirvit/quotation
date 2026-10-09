<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Allow the request only when the signed-in user has one of the given roles.
     *
     * Usage: ->middleware('role:studio_admin,staff')
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->user()?->role;

        if (! $role instanceof UserRole || ! in_array($role->value, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
