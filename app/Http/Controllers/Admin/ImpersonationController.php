<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ImpersonationController extends Controller
{
    public const SESSION_KEY = 'impersonator_id';

    /**
     * Sign the super admin in as the studio's first active admin, for support.
     */
    public function start(Request $request, Studio $studio): RedirectResponse
    {
        Gate::authorize('impersonate', $studio);

        $studioAdmin = $studio->admins()->where('is_active', true)->orderBy('id')->first();

        if ($studioAdmin === null) {
            return back()->with('status', "{$studio->name} has no active admin to sign in as.");
        }

        $superAdminId = $request->user()->id;

        Auth::guard('web')->login($studioAdmin);
        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, $superAdminId);

        return redirect()->route('studio.dashboard');
    }

    /**
     * Return from a studio session to the super admin account.
     */
    public function stop(Request $request): RedirectResponse
    {
        $superAdmin = User::find($request->session()->pull(self::SESSION_KEY));

        if ($superAdmin === null || ! $superAdmin->isSuperAdmin()) {
            abort(403);
        }

        $studioId = $request->user()->studio_id;

        Auth::guard('web')->login($superAdmin);
        $request->session()->regenerate();

        return $studioId
            ? redirect()->route('admin.studios.edit', $studioId)
            : redirect()->route('admin.dashboard');
    }
}
