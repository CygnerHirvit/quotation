<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['studio_id', 'name', 'email', 'role', 'phone', 'is_active', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Studio, $this>
     */
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isStudioAdmin(): bool
    {
        return $this->role === UserRole::StudioAdmin;
    }

    /**
     * Whether the user may sign in: the account is active and, for studio users, the studio is not blocked.
     */
    public function canSignIn(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->isSuperAdmin() || ($this->studio?->isActive() ?? false);
    }

    /**
     * The page a user lands on after signing in.
     */
    public function homeRoute(): string
    {
        return $this->isSuperAdmin() ? 'admin.dashboard' : 'studio.dashboard';
    }
}
