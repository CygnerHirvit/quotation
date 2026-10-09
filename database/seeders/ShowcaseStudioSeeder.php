<?php

namespace Database\Seeders;

use App\Enums\FunctionSide;
use App\Enums\GstMode;
use App\Enums\ItemSection;
use App\Enums\PortfolioType;
use App\Enums\PriceCategory;
use App\Enums\QuotationTemplate;
use App\Enums\StudioTheme;
use App\Enums\UserRole;
use App\Models\PortfolioItem;
use App\Models\PriceItem;
use App\Models\Quotation;
use App\Models\Studio;
use App\Models\TermTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * A complete test studio with one wedding quotation that uses every feature.
 *
 * Run: php artisan db:seed --class=ShowcaseStudioSeeder
 * Running it again replaces the showcase studio; other studios are not touched. Every password is "password".
 */
class ShowcaseStudioSeeder extends Seeder
{
    private const SLUG = 'shubh-vivah-films';

    public function run(): void
    {
        DB::transaction(function (): void {
            $this->removePreviousShowcase();

            $studio = $this->createStudio();
            [$admin, $staff] = $this->createTeam($studio);
            $this->createPriceList($studio);
            $this->createPortfolio($studio);
            $this->createTerms($studio);
            $quotation = $this->createWeddingQuotation($studio, $staff);

            $this->command?->info('Showcase studio ready: '.$studio->name);
            $this->command?->line('  Studio admin: '.$admin->email.' / password');
            $this->command?->line('  Staff:        '.$staff->email.' / password');
            $this->command?->line('  Quotation:    '.$quotation->number.' · '.$quotation->displayName());
            $this->command?->line('  Client link:  '.$quotation->publicUrl());
        });
    }

    private function removePreviousShowcase(): void
    {
        $previous = Studio::where('slug', self::SLUG)->first();

        if ($previous === null) {
            return;
        }

        Storage::disk('public')->deleteDirectory('showcase');
        Quotation::withoutGlobalScopes()->where('studio_id', $previous->id)->forceDelete();
        $previous->delete();
    }

    private function createStudio(): Studio
    {
        return Studio::create([
            'name' => 'Shubh Vivah Films',
            'slug' => self::SLUG,
            'tagline' => 'Your story, told the way you will want to remember it.',
            'email' => 'hello@shubhvivah.test',
            'phone' => '+91 98250 11223',
            'city' => 'Ahmedabad',
            'address' => '3rd Floor, Shivalik Arcade, CG Road, Ahmedabad 380009',
            'signature_name' => 'Rohan Mehta',
            'theme' => StudioTheme::TealMarigold,
            'quote_prefix' => 'SVF',
            'gst_percent' => 18,
            'validity_days' => 15,
            'show_breakdown_default' => false,
            'notify_email_on_accept' => true,
            'whatsapp_message' => "Namaste {client} 🙏\nThank you for considering {studio}. Here is your wedding quotation {number}:\n{link}\n\nYou can accept it online from the same page.",
            'logo_path' => $this->makeLogo(),
            'cover_wedding_path' => $this->makeCover('cover-wedding.jpg', [15, 76, 74], [199, 116, 26]),
            'cover_corporate_path' => $this->makeCover('cover-corporate.jpg', [20, 48, 47], [95, 115, 114]),
            'status' => 'active',
        ]);
    }

    /**
     * @return array{User, User}
     */
    private function createTeam(Studio $studio): array
    {
        $admin = User::factory()->for($studio)->create([
            'name' => 'Rohan Mehta',
            'email' => 'admin@shubhvivah.test',
            'phone' => '+91 98250 11223',
            'role' => UserRole::StudioAdmin,
        ]);

        $staff = User::factory()->for($studio)->create([
            'name' => 'Priya Desai',
            'email' => 'staff@shubhvivah.test',
            'phone' => '+91 98250 44556',
            'role' => UserRole::Staff,
        ]);

        return [$admin, $staff];
    }

    private function createPriceList(Studio $studio): void
    {
        $items = [
            PriceCategory::Crew->value => [
                ['Candid Photographer', 18000, 'Senior', null, null],
                ['Candid Photographer', 13000, 'Mid', null, null],
                ['Traditional Photographer', 8000, null, null, null],
                ['Cinematographer', 20000, 'Senior', null, null],
                ['Cinematographer', 14000, 'Mid', null, null],
                ['Traditional Videographer', 9000, null, null, null],
                ['Drone Pilot', 12000, null, 'Subject to permissions', null],
                ['Drone Pilot + FPV', 22000, null, null, null],
                ['Live Streaming (2 cameras)', 25000, null, null, null],
                ['LED Wall with Live (1 screen)', 65000, null, null, null],
                ['Photo Booth with Instant Prints', 30000, null, 'Old, discontinued setup', null],
            ],
            PriceCategory::Video->value => [
                ['Pre-wedding Film', 25000, null, '3–4 minutes', '30 days'],
                ['Wedding Highlight', 18000, null, '5–7 minutes', '45 days'],
                ['Wedding Full Film', 9000, null, 'Per function, full length', '60 days'],
                ['Sangeet Full Film', 16000, null, 'Multi-camera edit', '60 days'],
                ['Haldi & Mehendi Full Film', 8000, null, null, '60 days'],
                ['Instagram Reel', 2000, null, 'Vertical, 30–60 seconds', '15 days'],
                ['Same-day Edit', 35000, null, 'Played at the reception', 'Same day'],
            ],
            PriceCategory::Photo->value => [
                ['All Couple Photos Edited', 12000, null, 'Colour graded', '30 days'],
                ['Online Gallery Link', 5000, null, 'valid for 2 years', '7 days'],
                ['Face Recognition Link', 5000, null, 'valid for 7 days', '3 days'],
                ['Premium Album (40 pages)', 22000, null, 'Lay-flat, 12×36', '45 days after selection'],
            ],
        ];

        foreach ($items as $category => $rows) {
            foreach ($rows as [$name, $rate, $label, $note, $delivery]) {
                PriceItem::create([
                    'studio_id' => $studio->id,
                    'category' => $category,
                    'name' => $name,
                    'rate' => $rate,
                    'internal_label' => $label,
                    'note' => $note === 'Old, discontinued setup' ? null : $note,
                    'delivery' => $delivery,
                    // One hidden item shows the "Hide from quotations" switch.
                    'is_active' => $note !== 'Old, discontinued setup',
                ]);
            }
        }
    }

    private function createPortfolio(Studio $studio): void
    {
        $albums = [
            ['Wedding · Riya & Karan', [176, 72, 52], [242, 196, 120]],
            ['Pre-wedding · Udaipur', [40, 92, 110], [230, 205, 160]],
            ['Engagement · Nisha & Dev', [120, 60, 90], [245, 200, 190]],
        ];

        foreach ($albums as $index => [$title, $from, $to]) {
            PortfolioItem::create([
                'studio_id' => $studio->id,
                'type' => PortfolioType::Album,
                'title' => $title,
                'url' => 'https://example.com/galleries/'.str($title)->slug(),
                'cover_path' => $this->makeCover('album-'.($index + 1).'.jpg', $from, $to, 900, 675),
            ]);
        }

        // Placeholder YouTube links: the page shows each video's own thumbnail.
        foreach (['Wedding Film · Riya & Karan', 'Pre-wedding Teaser · Udaipur'] as $title) {
            PortfolioItem::create([
                'studio_id' => $studio->id,
                'type' => PortfolioType::Video,
                'title' => $title,
                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ]);
        }

        PortfolioItem::create([
            'studio_id' => $studio->id,
            'type' => PortfolioType::Video,
            'title' => 'Old showreel (hidden)',
            'url' => 'https://vimeo.com/76979871',
            'is_active' => false,
        ]);
    }

    private function createTerms(Studio $studio): void
    {
        foreach (QuotationTemplate::cases() as $template) {
            foreach (TermTemplate::starterTerms($template) as $term) {
                TermTemplate::create([...$term, 'studio_id' => $studio->id, 'template' => $template]);
            }
        }
    }

    /**
     * A full wedding: six functions with dates and crew, video and photo deliverables (one not included),
     * a discount, GST added, a negotiated final amount, the price breakdown shown, an extra term and internal notes.
     */
    private function createWeddingQuotation(Studio $studio, User $staff): Quotation
    {
        $price = fn (string $name, ?string $label = null) => PriceItem::where('studio_id', $studio->id)
            ->where('name', $name)
            ->when($label, fn ($query) => $query->where('internal_label', $label))
            ->firstOrFail();

        $quotation = Quotation::createDraft($staff);

        $quotation->update([
            'bride' => 'Aarohi',
            'groom' => 'Vivaan',
            'client_phone' => '98765 43210',
            'venue' => 'The Grand Bhagwati, SG Highway',
            'city' => 'Ahmedabad',
            'dates_tbd' => false,
            'start_date' => '2027-02-04',
            'end_date' => '2027-02-07',
            'function_side' => FunctionSide::BothSides,
            'discount_percent' => 5,
            'gst_mode' => GstMode::Included,
            'gst_percent' => 18,
            'show_breakdown' => true,
            'internal_notes' => "Referred by Riya & Karan.\nGroom's father asked for a family discount, approved 5%.\nRounded the final amount down for the booking.",
            'valid_until' => now()->addDays(15),
        ]);

        $functions = [
            ['Pre-wedding Shoot', '2027-01-10', 'Polo Forest, Vijaynagar', 'Full day outdoor shoot', [
                ['Candid Photographer', 'Senior', 1], ['Cinematographer', 'Senior', 1], ['Drone Pilot', null, 1],
            ]],
            ['Haldi', '2027-02-04', 'Bride\'s home, Bodakdev', 'Morning', [
                ['Candid Photographer', 'Mid', 1], ['Traditional Photographer', null, 1], ['Cinematographer', 'Mid', 1],
            ]],
            ['Mehendi', '2027-02-04', 'Bride\'s home, Bodakdev', 'Evening', [
                ['Candid Photographer', 'Mid', 1], ['Traditional Videographer', null, 1],
            ]],
            ['Sangeet & Garba', '2027-02-05', 'The Grand Bhagwati – Lawn', null, [
                ['Candid Photographer', 'Senior', 1], ['Candid Photographer', 'Mid', 1], ['Cinematographer', 'Senior', 1],
                ['Cinematographer', 'Mid', 1], ['Traditional Videographer', null, 1], ['Drone Pilot + FPV', null, 1],
                ['LED Wall with Live (1 screen)', null, 1],
            ]],
            ['Wedding', '2027-02-06', 'The Grand Bhagwati – Banquet', 'Baraat to Vidaai', [
                ['Candid Photographer', 'Senior', 2], ['Traditional Photographer', null, 1], ['Cinematographer', 'Senior', 1],
                ['Cinematographer', 'Mid', 1], ['Traditional Videographer', null, 1], ['Drone Pilot', null, 1],
                ['Live Streaming (2 cameras)', null, 1],
            ]],
            ['Reception', '2027-02-07', 'The Grand Bhagwati – Banquet', null, [
                ['Candid Photographer', 'Senior', 1], ['Traditional Photographer', null, 1], ['Cinematographer', 'Mid', 1],
            ]],
        ];

        foreach ($functions as [$name, $date, $venue, $note, $crew]) {
            $event = $quotation->events()->create(['name' => $name, 'event_date' => $date, 'venue' => $venue, 'note' => $note]);

            foreach ($crew as [$role, $label, $qty]) {
                $item = $price($role, $label);
                $event->crew()->create(['price_item_id' => $item->id, 'name' => $item->name, 'rate' => $item->rate, 'qty' => $qty]);
            }
        }

        // A custom crew line that is not in the price list.
        $quotation->events()->where('name', 'Wedding')->firstOrFail()
            ->crew()->create(['name' => 'Ghodi & Baraat Gimbal Operator', 'rate' => 6000, 'qty' => 1]);

        $deliverables = [
            [ItemSection::Video, 'Pre-wedding Film', 1, true],
            [ItemSection::Video, 'Wedding Highlight', 1, true],
            [ItemSection::Video, 'Wedding Full Film', 1, true],
            [ItemSection::Video, 'Sangeet Full Film', 1, true],
            [ItemSection::Video, 'Haldi & Mehendi Full Film', 1, true],
            [ItemSection::Video, 'Instagram Reel', 6, true],
            [ItemSection::Video, 'Same-day Edit', 1, false],
            [ItemSection::Photo, 'All Couple Photos Edited', 1, true],
            [ItemSection::Photo, 'Online Gallery Link', 1, true],
            [ItemSection::Photo, 'Face Recognition Link', 1, true],
            [ItemSection::Photo, 'Premium Album (40 pages)', 2, true],
        ];

        foreach ($deliverables as [$section, $name, $qty, $included]) {
            $item = $price($name);

            $quotation->items()->create([
                'section' => $section,
                'price_item_id' => $item->id,
                'name' => $item->name,
                'rate' => $item->rate,
                'qty' => $qty,
                'note' => $item->note,
                'delivery' => $item->delivery,
                'included' => $included,
            ]);
        }

        // A custom photo item, not from the price list.
        $quotation->items()->create([
            'section' => ItemSection::Photo,
            'name' => 'Parents\' Mini Album (20 pages)',
            'rate' => 9000,
            'qty' => 2,
            'note' => 'One for each family',
            'delivery' => '45 days after selection',
        ]);

        // A term added only to this quotation.
        $quotation->terms()->create([
            'title' => 'Pre-wedding location',
            'body' => 'Entry fees and permits for the pre-wedding location are paid by the couple.',
        ]);

        $quotation->refreshCachedTotals();

        // Negotiated final amount: the calculated total rounded down to the nearest ₹10,000.
        $quotation->update(['manual_total' => (int) (floor($quotation->totals()->calculated / 10000) * 10000)]);
        $quotation->markShared();
        $quotation->refreshCachedTotals();

        return $quotation->refresh();
    }

    // Placeholder images --------------------------------------------------------------------------

    private function makeLogo(): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $size = 400;
        $image = imagecreatetruecolor($size, $size);
        imagefill($image, 0, 0, imagecolorallocate($image, 15, 76, 74));
        imagefilledellipse($image, $size / 2, $size / 2, 330, 330, imagecolorallocate($image, 199, 116, 26));
        imagefilledellipse($image, $size / 2, $size / 2, 300, 300, imagecolorallocate($image, 15, 76, 74));

        $this->writeCentred($image, 'SV', 120, imagecolorallocate($image, 252, 233, 194), $size, $size);

        return $this->store($image, 'logo.png');
    }

    /**
     * @param  array{int, int, int}  $from
     * @param  array{int, int, int}  $to
     */
    private function makeCover(string $name, array $from, array $to, int $width = 1600, int $height = 900): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $image = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $colour = imagecolorallocate(
                $image,
                (int) ($from[0] + ($to[0] - $from[0]) * $ratio),
                (int) ($from[1] + ($to[1] - $from[1]) * $ratio),
                (int) ($from[2] + ($to[2] - $from[2]) * $ratio),
            );
            imageline($image, 0, $y, $width, $y, $colour);
        }

        // Soft circles, like out-of-focus lights.
        mt_srand(crc32($name));
        for ($i = 0; $i < 18; $i++) {
            $glow = imagecolorallocatealpha($image, 255, 236, 200, mt_rand(95, 118));
            $diameter = mt_rand((int) ($width * 0.04), (int) ($width * 0.16));
            imagefilledellipse($image, mt_rand(0, $width), mt_rand(0, $height), $diameter, $diameter, $glow);
        }

        return $this->store($image, $name);
    }

    private function writeCentred(\GdImage $image, string $text, int $size, int $colour, int $width, int $height): void
    {
        $font = collect(['C:/Windows/Fonts/georgiab.ttf', 'C:/Windows/Fonts/georgia.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf'])
            ->first(fn (string $path) => is_file($path));

        if ($font && function_exists('imagettfbbox')) {
            $box = imagettfbbox($size, 0, $font, $text);
            $textWidth = $box[2] - $box[0];
            $textHeight = $box[1] - $box[7];
            imagettftext($image, $size, 0, (int) (($width - $textWidth) / 2), (int) (($height + $textHeight) / 2), $colour, $font, $text);

            return;
        }

        imagestring($image, 5, (int) ($width / 2 - 8), (int) ($height / 2 - 8), $text, $colour);
    }

    private function store(\GdImage $image, string $name): string
    {
        ob_start();
        str_ends_with($name, '.png') ? imagepng($image) : imagejpeg($image, null, 85);
        $contents = ob_get_clean();
        imagedestroy($image);

        $path = 'showcase/'.$name;
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
