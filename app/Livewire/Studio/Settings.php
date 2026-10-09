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

    public function save(): void
    {
        $this->authorize('manageSettings', $this->form->studio);

        $this->form->save();

        session()->flash('status', 'Settings saved.');
    }

    public function removeImage(string $column): void
    {
        $this->authorize('manageSettings', $this->form->studio);

        $this->form->removeImage($column);

        session()->flash('status', 'Image removed.');
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
