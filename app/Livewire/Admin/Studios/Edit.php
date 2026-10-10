<?php

namespace App\Livewire\Admin\Studios;

use App\Enums\StudioStatus;
use App\Enums\StudioTheme;
use App\Enums\UserRole;
use App\Livewire\Forms\StudioForm;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Edit studio')]
class Edit extends Component
{
    use WithFileUploads;

    public Studio $studio;

    public StudioForm $form;

    public string $newAdminName = '';

    public string $newAdminEmail = '';

    public string $newAdminPassword = '';

    public ?int $passwordUserId = null;

    public string $newPassword = '';

    public function mount(Studio $studio): void
    {
        $this->authorize('update', $studio);

        $this->studio = $studio;
        $this->form->setStudio($studio);
    }

    public function updatedFormLogo(): void
    {
        $this->form->removeLogo = false;
        $this->validateOnly('form.logo');
    }

    public function clearSelectedLogo(): void
    {
        $this->form->clearLogo();
        $this->resetValidation('form.logo');
    }

    public function markLogoForRemoval(): void
    {
        $this->form->markLogoForRemoval();
        $this->resetValidation('form.logo');
    }

    public function undoLogoRemoval(): void
    {
        $this->form->undoLogoRemoval();
    }

    public function save(): void
    {
        $this->authorize('update', $this->studio);

        $this->studio = $this->form->save();

        session()->flash('status', 'Studio details saved.');
    }

    public function addAdmin(): void
    {
        $this->authorize('update', $this->studio);

        $validated = $this->validate([
            'newAdminName' => ['required', 'string', 'max:120'],
            'newAdminEmail' => ['required', 'email', 'max:120', 'unique:users,email'],
            'newAdminPassword' => ['required', Password::defaults()],
        ], attributes: [
            'newAdminName' => 'name',
            'newAdminEmail' => 'email',
            'newAdminPassword' => 'password',
        ]);

        $this->studio->users()->create([
            'name' => $validated['newAdminName'],
            'email' => $validated['newAdminEmail'],
            'password' => $validated['newAdminPassword'],
            'role' => UserRole::StudioAdmin,
            'is_active' => true,
        ]);

        $this->reset('newAdminName', 'newAdminEmail', 'newAdminPassword');

        session()->flash('status', 'Studio admin added.');
    }

    public function startPasswordReset(int $userId): void
    {
        $this->passwordUserId = $this->studioUser($userId)->id;
        $this->reset('newPassword');
        $this->resetValidation('newPassword');
    }

    public function cancelPasswordReset(): void
    {
        $this->reset('passwordUserId', 'newPassword');
    }

    public function savePassword(): void
    {
        $this->authorize('update', $this->studio);

        $this->validate(['newPassword' => ['required', Password::defaults()]], attributes: ['newPassword' => 'new password']);

        $user = $this->studioUser((int) $this->passwordUserId);
        $user->update(['password' => $this->newPassword]);

        $this->reset('passwordUserId', 'newPassword');

        session()->flash('status', "Password changed for {$user->name}.");
    }

    public function toggleUserActive(int $userId): void
    {
        $this->authorize('update', $this->studio);

        $user = $this->studioUser($userId);
        $user->update(['is_active' => ! $user->is_active]);

        session()->flash('status', $user->is_active ? "{$user->name} can sign in again." : "{$user->name} can no longer sign in.");
    }

    /**
     * Find a user that belongs to this studio, so ids from the browser cannot reach other studios.
     */
    protected function studioUser(int $userId): User
    {
        return $this->studio->users()->findOrFail($userId);
    }

    public function render(): View
    {
        return view('livewire.admin.studios.edit', [
            'users' => $this->studio->users()->orderBy('role')->orderBy('name')->get(),
            'themes' => StudioTheme::cases(),
            'statuses' => StudioStatus::cases(),
        ]);
    }
}
