<?php

namespace App\Livewire\Forms;

use App\Enums\StudioStatus;
use App\Enums\StudioTheme;
use App\Models\Studio;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class StudioForm extends Form
{
    public ?Studio $studio = null;

    public string $name = '';

    public string $city = '';

    public string $phone = '';

    public string $email = '';

    public string $quotePrefix = '';

    public string $theme = StudioTheme::TealMarigold->value;

    public string $status = StudioStatus::Active->value;

    /** @var TemporaryUploadedFile|null */
    public $logo = null;

    public bool $removeLogo = false;

    public function setStudio(Studio $studio): void
    {
        $this->studio = $studio;
        $this->name = $studio->name;
        $this->city = (string) $studio->city;
        $this->phone = (string) $studio->phone;
        $this->email = (string) $studio->email;
        $this->quotePrefix = $studio->quote_prefix;
        $this->theme = $studio->theme->value;
        $this->status = $studio->status->value;
        $this->logo = null;
        $this->removeLogo = false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'quotePrefix' => ['required', 'string', 'alpha_num', 'min:2', 'max:6'],
            'theme' => ['required', Rule::enum(StudioTheme::class)],
            'status' => ['required', Rule::enum(StudioStatus::class)],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'quotePrefix' => 'quotation prefix',
            'logo' => 'studio logo',
        ];
    }

    public function clearLogo(): void
    {
        $this->logo = null;
    }

    public function markLogoForRemoval(): void
    {
        $this->removeLogo = true;
        $this->logo = null;
    }

    public function undoLogoRemoval(): void
    {
        $this->removeLogo = false;
    }

    /**
     * Validate and save the studio, creating it when the form has no studio yet.
     */
    public function save(): Studio
    {
        $this->validate();

        $studio = $this->studio ?? new Studio(['slug' => $this->uniqueSlug()]);

        $studio->fill([
            'name' => $this->name,
            'city' => $this->city ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'quote_prefix' => Str::upper($this->quotePrefix),
            'theme' => $this->theme,
            'status' => $this->status,
        ]);

        $studio->save();

        if ($this->removeLogo) {
            if ($studio->logo_path) {
                $previous = $studio->logo_path;
                $studio->update(['logo_path' => null]);

                if (Storage::disk('public')->exists($previous)) {
                    Storage::disk('public')->delete($previous);
                }
            }
        } elseif ($this->logo !== null) {
            $previous = $studio->logo_path;
            $path = $this->logo->store('studios/logos', 'public');
            $studio->update(['logo_path' => $path]);

            if ($previous && Storage::disk('public')->exists($previous)) {
                Storage::disk('public')->delete($previous);
            }
        }

        $studio->refresh();
        $this->studio = $studio;
        $this->logo = null;
        $this->removeLogo = false;

        return $studio;
    }

    protected function uniqueSlug(): string
    {
        $base = Str::slug($this->name) ?: 'studio';
        $slug = $base;
        $suffix = 2;

        while (Studio::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
