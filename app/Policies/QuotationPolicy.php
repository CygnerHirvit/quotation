<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;

/**
 * Studio admins and staff work on every quotation of their own studio.
 */
class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->studio_id !== null;
    }

    public function create(User $user): bool
    {
        return $user->studio_id !== null;
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->studio_id !== null && $user->studio_id === $quotation->studio_id;
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $this->update($user, $quotation);
    }
}
