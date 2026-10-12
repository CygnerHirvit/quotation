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

    public ?string $signatureData = null;

    public bool $removeLogo = false;

    public bool $removeCoverWedding = false;

    public bool $removeCoverCorporate = false;

    public bool $removeAuthorizedSignature = false;

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
        $this->logo = null;
        $this->coverWedding = null;
        $this->coverCorporate = null;
        $this->signatureData = null;
        $this->removeLogo = false;
        $this->removeCoverWedding = false;
        $this->removeCoverCorporate = false;
        $this->removeAuthorizedSignature = false;
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
            'signatureData' => ['nullable', 'string'],
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
            'signatureData' => 'authorized signature',
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

        $this->processImage('logo', 'removeLogo', 'logo_path', 'studios/logos');
        $this->processImage('coverWedding', 'removeCoverWedding', 'cover_wedding_path', 'studios/covers');
        $this->processImage('coverCorporate', 'removeCoverCorporate', 'cover_corporate_path', 'studios/covers');

        // Handle drawn e-signature removal or new signature data URL
        if ($this->removeAuthorizedSignature) {
            if ($this->studio->authorized_signature_path) {
                $previous = $this->studio->authorized_signature_path;
                $this->studio->authorized_signature_path = null;
                if (Storage::disk('public')->exists($previous)) {
                    Storage::disk('public')->delete($previous);
                }
            }
        } elseif ($this->signatureData && str_starts_with($this->signatureData, 'data:image/png;base64,')) {
            $bytes = base64_decode(substr($this->signatureData, strlen('data:image/png;base64,')), true);
            if ($bytes !== false) {
                $previous = $this->studio->authorized_signature_path;
                $path = 'studios/'.$this->studio->id.'/signature-'.uniqid().'.png';
                Storage::disk('public')->put($path, $bytes);
                $this->studio->authorized_signature_path = $path;

                if ($previous && Storage::disk('public')->exists($previous)) {
                    Storage::disk('public')->delete($previous);
                }
            }
        }

        $this->studio->save();

        $this->logo = null;
        $this->coverWedding = null;
        $this->coverCorporate = null;
        $this->signatureData = null;
        $this->removeLogo = false;
        $this->removeCoverWedding = false;
        $this->removeCoverCorporate = false;
        $this->removeAuthorizedSignature = false;
    }

    public function clearSelected(string $property): void
    {
        if (in_array($property, ['logo', 'coverWedding', 'coverCorporate'], true)) {
            $this->{$property} = null;
        } elseif ($property === 'authorizedSignature' || $property === 'signatureData') {
            $this->signatureData = null;
        }
    }

    public function markForRemoval(string $property): void
    {
        $removeProperty = 'remove'.ucfirst($property);
        if (property_exists($this, $removeProperty)) {
            $this->{$removeProperty} = true;
            $this->{$property} = null;
        }
    }

    public function undoRemoval(string $property): void
    {
        $removeProperty = 'remove'.ucfirst($property);
        if (property_exists($this, $removeProperty)) {
            $this->{$removeProperty} = false;
        }
    }

    public function isMarkedForRemoval(string $property): bool
    {
        $removeProperty = 'remove'.ucfirst($property);

        return property_exists($this, $removeProperty) && (bool) $this->{$removeProperty};
    }

    protected function processImage(string $property, string $removeProperty, string $column, string $directory): void
    {
        if ($this->{$removeProperty}) {
            if ($this->studio->{$column}) {
                $previous = $this->studio->{$column};
                $this->studio->{$column} = null;

                if (Storage::disk('public')->exists($previous)) {
                    Storage::disk('public')->delete($previous);
                }
            }
        } elseif ($this->{$property} !== null) {
            $previous = $this->studio->{$column};
            $path = $this->{$property}->store($directory, 'public');

            $this->studio->{$column} = $path;

            if ($previous && Storage::disk('public')->exists($previous)) {
                Storage::disk('public')->delete($previous);
            }
        }
    }
}
