<?php

namespace App\Models;

use App\Enums\StudioStatus;
use App\Enums\StudioTheme;
use App\Enums\UserRole;
use Database\Factories\StudioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'logo_path', 'tagline', 'email', 'phone', 'city', 'address',
    'signature_name', 'authorized_signature_path', 'theme', 'accent_color', 'quote_prefix', 'gst_percent',
    'validity_days', 'show_breakdown_default', 'whatsapp_message', 'notify_email_on_accept',
    'cover_wedding_path', 'cover_corporate_path', 'status',
])]
class Studio extends Model
{
    /** @use HasFactory<StudioFactory> */
    use HasFactory;

    /**
     * Used when the studio has not written its own WhatsApp message.
     * Placeholders: {client}, {studio}, {number}, {link}.
     */
    public const DEFAULT_WHATSAPP_MESSAGE = "Hi {client}, here is your quotation {number} from {studio}:\n{link}";

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'theme' => StudioTheme::class,
            'status' => StudioStatus::class,
            'gst_percent' => 'decimal:2',
            'show_breakdown_default' => 'boolean',
            'notify_email_on_accept' => 'boolean',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function admins(): HasMany
    {
        return $this->users()->where('role', UserRole::StudioAdmin);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function authorizedSignatureUrl(): ?string
    {
        return $this->authorized_signature_path ? Storage::disk('public')->url($this->authorized_signature_path) : null;
    }

    public function coverUrl(string $template): ?string
    {
        $path = $template === 'corporate' ? $this->cover_corporate_path : $this->cover_wedding_path;

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /**
     * The theme's colours, with the studio's own accent colour applied when it has one.
     *
     * @return array<string, string>
     */
    public function themeTokens(): array
    {
        $tokens = $this->theme->tokens();

        if ($this->accent_color) {
            $tokens['accent'] = $this->accent_color;
            $tokens['highlight'] = $this->accent_color;
            $tokens['on-accent'] = self::isLightColour($this->accent_color) ? '#111111' : '#FFFFFF';
        }

        return $tokens;
    }

    /**
     * Whether dark text reads better than white text on this #RRGGBB colour.
     */
    public static function isLightColour(string $hex): bool
    {
        [$red, $green, $blue] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');

        return (0.299 * $red + 0.587 * $green + 0.114 * $blue) > 160;
    }

    public function whatsappTemplate(): string
    {
        return $this->whatsapp_message ?: self::DEFAULT_WHATSAPP_MESSAGE;
    }

    public function initial(): string
    {
        return Str::upper(Str::substr($this->name, 0, 1));
    }

    public function isActive(): bool
    {
        return $this->status === StudioStatus::Active;
    }
}
