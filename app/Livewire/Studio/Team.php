<?php

namespace App\Livewire\Studio;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Team')]
class Team extends Component
{
    /** Id of the user being edited, or 0 for a new user, or null when the form is closed. */
    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $role = UserRole::Staff->value;

    public string $password = '';

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function create(): void
    {
        $this->authorize('create', User::class);

        $this->resetForm();
        $this->editingId = 0;
    }

    public function edit(int $userId): void
    {
        $user = $this->teamMember($userId);

        $this->authorize('update', $user);

        $this->resetForm();
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = (string) $user->phone;
        $this->role = $user->role->value;
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $user = $this->editingId ? $this->teamMember($this->editingId) : null;

        $this->authorize($user ? 'update' : 'create', $user ?? User::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in([UserRole::StudioAdmin->value, UserRole::Staff->value])],
            'password' => [$user ? 'nullable' : 'required', Password::defaults()],
        ]);

        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
        ];

        // Admins cannot change their own role, so a studio always keeps at least one admin.
        if ($user === null || auth()->user()->can('changeAccess', $user)) {
            $attributes['role'] = $validated['role'];
        }

        if ($validated['password']) {
            $attributes['password'] = $validated['password'];
        }

        if ($user) {
            $user->update($attributes);
            session()->flash('status', "{$user->name} updated.");
        } else {
            $user = User::create([...$attributes, 'studio_id' => auth()->user()->studio_id, 'is_active' => true]);
            session()->flash('status', "{$user->name} can now sign in with {$user->email}.");
        }

        $this->resetForm();
    }

    public function toggleActive(int $userId): void
    {
        $user = $this->teamMember($userId);

        $this->authorize('changeAccess', $user);

        $user->update(['is_active' => ! $user->is_active]);

        session()->flash('status', $user->is_active ? "{$user->name} can sign in again." : "{$user->name} can no longer sign in.");
    }

    /**
     * Find a user of the signed-in admin's studio, so ids from the browser cannot reach other studios.
     */
    protected function teamMember(int $userId): User
    {
        return User::where('studio_id', auth()->user()->studio_id)->findOrFail($userId);
    }

    protected function resetForm(): void
    {
        $this->reset('editingId', 'name', 'email', 'phone', 'role', 'password');
        $this->resetValidation();
    }

    /**
     * @return Collection<int, User>
     */
    protected function members(): Collection
    {
        return User::where('studio_id', auth()->user()->studio_id)
            ->orderBy('role')
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.studio.team', [
            'members' => $this->members(),
            'roles' => [UserRole::StudioAdmin, UserRole::Staff],
        ]);
    }
}
