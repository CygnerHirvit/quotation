<?php

namespace App\Livewire\Forms;

use App\Enums\StudioTheme;
use App\Models\Studio;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

/**
 * The settings a studio admin manages for their own studio.
 * The quotation prefix and the studio status stay with the super admin.
 */
class StudioSettingsForm extends Form
{
    public Studio $studio;

    public string $name = '';

    public string $tagline = '';

    public string $email = '';

    public string $phone = '';

    public string $city = '';

    public string $address = '';

    public string $signatureName = '';

    public string $theme = StudioTheme::TealMarigold->value;

    public string $accentColor = '';

    public string $gstPercent = '18';

    public string $validityDays = '15';

    public bool $showBreakdownDefault = false;

    public string $whatsappMessage = '';

    public bool $notifyEmailOnAccept = true;

    /** @var TemporaryUploadedFile|null */
    public $logo = null;

    /** @var TemporaryUploadedFile|null */
    public $coverWedding = null;

    /** @var TemporaryUploadedFile|null */
    public $coverCorporate = null;

    public function setStudio(Studio $studio): void
    {
        $this->studio = $studio;
        $this->name = $studio->name;
        $this->tagline = (string) $studio->tagline;
        $this->email = (string) $studio->email;
        $this->phone = (string) $studio->phone;
        $this->city = (string) $studio->city;
        $this->address = (string) $studio->address;
        $this->signatureName = (string) $studio->signature_name;
        $this->theme = $studio->theme->value;
        $this->accentColor = (string) $studio->accent_color;
        $this->gstPercent = (string) (float) $studio->gst_percent;
        $this->validityDays = (string) $studio->validity_days;
        $this->showBreakdownDefault = $studio->show_breakdown_default;
        $this->whatsappMessage = $studio->whatsappTemplate();
        $this->notifyEmailOnAccept = (bool) $studio->notify_email_on_accept;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $image = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'];

        return [
            'name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:500'],
            'signatureName' => ['nullable', 'string', 'max:120'],
            'theme' => ['required', Rule::enum(StudioTheme::class)],
            'accentColor' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'gstPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'validityDays' => ['required', 'integer', 'min:1', 'max:365'],
            'showBreakdownDefault' => ['boolean'],
            'notifyEmailOnAccept' => ['boolean'],
            'whatsappMessage' => ['required', 'string', 'max:1000', 'regex:/\{link\}/'],
            'logo' => [...$image, 'max:2048'],
            'coverWedding' => [...$image, 'max:5120'],
            'coverCorporate' => [...$image, 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'accentColor.regex' => 'Use a colour code like #0F4C4A.',
            'whatsappMessage.regex' => 'The message must contain {link} so the client gets the quotation link.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'signatureName' => 'signature name',
            'accentColor' => 'accent colour',
            'gstPercent' => 'GST %',
            'validityDays' => 'validity days',
            'whatsappMessage' => 'WhatsApp message',
            'coverWedding' => 'wedding cover',
            'coverCorporate' => 'corporate cover',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->studio->fill([
            'name' => $this->name,
            'tagline' => $this->tagline ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'city' => $this->city ?: null,
            'address' => $this->address ?: null,
            'signature_name' => $this->signatureName ?: null,
            'theme' => $this->theme,
            'accent_color' => $this->accentColor ? strtoupper($this->accentColor) : null,
            'gst_percent' => $this->gstPercent,
            'validity_days' => (int) $this->validityDays,
            'show_breakdown_default' => $this->showBreakdownDefault,
            'whatsapp_message' => $this->whatsappMessage === Studio::DEFAULT_WHATSAPP_MESSAGE ? null : $this->whatsappMessage,
            'notify_email_on_accept' => $this->notifyEmailOnAccept,
        ]);

        $this->replaceImage('logo', 'logo_path', 'studios/logos');
        $this->replaceImage('coverWedding', 'cover_wedding_path', 'studios/covers');
        $this->replaceImage('coverCorporate', 'cover_corporate_path', 'studios/covers');

        $this->studio->save();
    }

    public function removeImage(string $column): void
    {
        if (! in_array($column, ['logo_path', 'cover_wedding_path', 'cover_corporate_path'], true)) {
            return;
        }

        if ($this->studio->{$column}) {
            Storage::disk('public')->delete($this->studio->{$column});
            $this->studio->update([$column => null]);
        }
    }

    /**
     * Store a newly uploaded image and delete the one it replaces.
     */
    protected function replaceImage(string $property, string $column, string $directory): void
    {
        if ($this->{$property} === null) {
            return;
        }

        $previous = $this->studio->{$column};
        $this->studio->{$column} = $this->{$property}->store($directory, 'public');
        $this->{$property} = null;

        if ($previous) {
            Storage::disk('public')->delete($previous);
        }
    }
}
