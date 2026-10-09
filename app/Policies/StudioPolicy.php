<?php

namespace App\Policies;

use App\Models\Studio;
use App\Models\User;

/**
 * Studios are created and managed by the super admin only.
 */
class StudioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Studio $studio): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Studio $studio): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Change the studio's own settings: branding, defaults, theme. Done by that studio's admins.
     */
    public function manageSettings(User $user, Studio $studio): bool
    {
        return $user->isStudioAdmin() && $user->studio_id === $studio->id;
    }

    /**
     * Sign in as the studio's admin for support. Only active studios can be entered.
     */
    public function impersonate(User $user, Studio $studio): bool
    {
        return $user->isSuperAdmin() && $studio->isActive();
    }
}
