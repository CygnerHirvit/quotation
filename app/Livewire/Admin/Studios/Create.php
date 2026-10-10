<?php

namespace App\Livewire\Admin\Studios;

use App\Enums\StudioTheme;
use App\Enums\UserRole;
use App\Livewire\Forms\StudioForm;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('New studio')]
class Create extends Component
{
    use WithFileUploads;

    public StudioForm $form;

    public string $adminName = '';

    public string $adminEmail = '';

    public string $adminPassword = '';

    public function mount(): void
    {
        $this->authorize('create', Studio::class);
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

    public function save(): void
    {
        $this->authorize('create', Studio::class);

        $admin = $this->validate([
            'adminName' => ['required', 'string', 'max:120'],
            'adminEmail' => ['required', 'email', 'max:120', 'unique:users,email'],
            'adminPassword' => ['required', Password::defaults()],
        ], attributes: [
            'adminName' => 'admin name',
            'adminEmail' => 'admin email',
            'adminPassword' => 'admin password',
        ]);

        $studio = DB::transaction(function () use ($admin) {
            $studio = $this->form->save();

            User::create([
                'studio_id' => $studio->id,
                'name' => $admin['adminName'],
                'email' => $admin['adminEmail'],
                'password' => $admin['adminPassword'],
                'role' => UserRole::StudioAdmin,
                'is_active' => true,
            ]);

            $studio->update(['signature_name' => $admin['adminName']]);

            return $studio;
        });

        session()->flash('status', "{$studio->name} is ready. Share the admin login with the studio.");

        $this->redirectRoute('admin.studios.edit', $studio, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.studios.create', [
            'themes' => StudioTheme::cases(),
        ]);
    }
}
