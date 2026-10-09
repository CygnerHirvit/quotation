<?php

namespace App\Policies;

use App\Models\User;

/**
 * Studio admins manage the logins of their own studio. Super admins manage studio users from the studio pages.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStudioAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isStudioAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isStudioAdmin() && $user->studio_id === $model->studio_id;
    }

    /**
     * Turn a login off or change its role. Admins cannot do this to themselves, so a studio never locks itself out.
     */
    public function changeAccess(User $user, User $model): bool
    {
        return $this->update($user, $model) && $user->id !== $model->id;
    }
}
