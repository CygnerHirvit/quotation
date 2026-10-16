<?php

namespace App\Livewire\Studio;

use App\Enums\StudioTheme;
use App\Livewire\Forms\StudioSettingsForm;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Studio settings')]
class Settings extends Component
{
    use WithFileUploads;

    public StudioSettingsForm $form;

    public function mount(): void
    {
        $studio = auth()->user()->studio;

        $this->authorize('manageSettings', $studio);

        $this->form->setStudio($studio);
    }

    public function updatedFormLogo(): void
    {
        $this->form->removeLogo = false;
        $this->validateOnly('form.logo');
    }

    public function updatedFormCoverWedding(): void
    {
        $this->form->removeCoverWedding = false;
        $this->validateOnly('form.coverWedding');
    }

    public function updatedFormCoverCorporate(): void
    {
        $this->form->removeCoverCorporate = false;
        $this->validateOnly('form.coverCorporate');
    }

    public function updatedFormTheme(): void
    {
        $this->form->accentColor = '';
    }

    public function clearSelectedImage(string $property): void
    {
        $this->form->clearSelected($property);
        $this->resetValidation("form.{$property}");
    }

    public function markImageForRemoval(string $property): void
    {
        $this->form->markForRemoval($property);
        $this->resetValidation("form.{$property}");
    }

    public function undoImageRemoval(string $property): void
    {
        $this->form->undoRemoval($property);
    }

    public function removeImage(string $column): void
    {
        $property = match ($column) {
            'logo_path' => 'logo',
            'cover_wedding_path' => 'coverWedding',
            'cover_corporate_path' => 'coverCorporate',
            'authorized_signature_path' => 'authorizedSignature',
            default => null,
        };

        if ($property) {
            $this->markImageForRemoval($property);
        }
    }

    public function save(): void
    {
        $this->authorize('manageSettings', $this->form->studio);

        $this->form->save();

        $this->dispatch('saved');

        session()->flash('status', 'Settings saved.');
    }

    public function resetWhatsappMessage(): void
    {
        $this->form->whatsappMessage = $this->form->studio::DEFAULT_WHATSAPP_MESSAGE;
    }

    public function render(): View
    {
        $studio = $this->form->studio;

        return view('livewire.studio.settings', [
            'themes' => StudioTheme::cases(),
            'whatsappPreview' => strtr($this->form->whatsappMessage, [
                '{client}' => 'Dhruty & Himanshu',
                '{studio}' => $this->form->name ?: $studio->name,
                '{number}' => $studio->quote_prefix.'-'.now()->year.'-001',
                '{link}' => url('/q/abc123'),
            ]),
        ]);
    }
}
