<?php

namespace Database\Seeders;

use App\Enums\FunctionType;
use App\Enums\ItemSection;
use App\Enums\PortfolioType;
use App\Enums\PriceCategory;
use App\Enums\QuotationStatus;
use App\Enums\QuotationTemplate;
use App\Enums\UserRole;
use App\Models\PortfolioItem;
use App\Models\PriceItem;
use App\Models\Quotation;
use App\Models\Studio;
use App\Models\TermTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed a super admin and one demo studio with an admin and a staff login.
     * Every password is "password". For local use only.
     */
    public function run(): void
    {
        User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'super@quotation.test',
        ]);

        $studio = Studio::factory()->create([
            'name' => 'Demo Studio',
            'slug' => 'demo-studio',
            'tagline' => 'Every function, every emotion.',
            'email' => 'hello@demostudio.test',
            'city' => 'Rajkot',
            'quote_prefix' => 'DMS',
            'signature_name' => 'Demo Studio Admin',
        ]);

        User::factory()->for($studio)->create([
            'name' => 'Demo Studio Admin',
            'email' => 'admin@demostudio.test',
            'role' => UserRole::StudioAdmin,
        ]);

        User::factory()->for($studio)->create([
            'name' => 'Demo Staff',
            'email' => 'staff@demostudio.test',
            'role' => UserRole::Staff,
        ]);

        $this->seedCatalog($studio);
        $this->seedQuotations($studio->users()->where('role', UserRole::StudioAdmin)->firstOrFail());
    }

    /**
     * Two sample quotations built from the demo price list.
     */
    private function seedQuotations(User $admin): void
    {
        $price = fn (string $name) => PriceItem::where('studio_id', $admin->studio_id)->where('name', $name)->firstOrFail();

        $wedding = Quotation::createDraft($admin);
        $wedding->update([
            'bride' => 'Dhruty',
            'groom' => 'Himanshu',
            'venue' => 'Imperial Palace',
            'dates_tbd' => false,
            'start_date' => '2026-12-11',
            'end_date' => '2026-12-13',
            'status' => QuotationStatus::Sent,
            'sent_at' => now(),
        ]);

        $functions = [
            'Engagement' => ['Candid Photographer' => 2, 'Cinematographer' => 1, 'Traditional Photographer' => 1],
            'Sangeet' => ['Candid Photographer' => 2, 'Cinematographer' => 2, 'Drone Pilot' => 1, 'LED Wall with Live (1 screen)' => 1],
            'Wedding' => ['Candid Photographer' => 2, 'Cinematographer' => 2, 'Traditional Videographer' => 1, 'Drone Pilot' => 1],
        ];

        foreach ($functions as $name => $crew) {
            $event = $wedding->events()->create(['name' => $name, 'venue' => 'Imperial Palace, Rajkot']);

            foreach ($crew as $role => $qty) {
                $item = $price($role);
                $event->crew()->create(['price_item_id' => $item->id, 'name' => $item->name, 'rate' => $item->rate, 'qty' => $qty]);
            }
        }

        foreach (['Wedding Highlight' => 1, 'Wedding Full Film' => 1, 'Sangeet Full Film' => 1, 'All Event Reels' => 6] as $name => $qty) {
            $item = $price($name);
            $wedding->items()->create(['section' => ItemSection::Video, 'price_item_id' => $item->id, 'name' => $item->name, 'rate' => $item->rate, 'qty' => $qty, 'delivery' => $item->delivery]);
        }

        foreach (['All Couple Photos Edited', 'Online Gallery Link', 'Face Recognition Link'] as $name) {
            $item = $price($name);
            $wedding->items()->create(['section' => ItemSection::Photo, 'price_item_id' => $item->id, 'name' => $item->name, 'rate' => $item->rate, 'note' => $item->note]);
        }

        $wedding->refreshCachedTotals();
        $wedding->update(['manual_total' => (int) (floor($wedding->totals()->calculated / 10000) * 10000)]);
        $wedding->refreshCachedTotals();

        $engagement = Quotation::createDraft($admin, FunctionType::Engagement);
        $engagement->update(['bride' => 'Krupali', 'groom' => 'Meet', 'dates_tbd' => false, 'start_date' => '2026-11-19']);
        $event = $engagement->events()->create(['name' => 'Engagement', 'venue' => 'Rajkot']);

        foreach (['Candid Photographer' => 1, 'Cinematographer' => 1] as $role => $qty) {
            $item = $price($role);
            $event->crew()->create(['price_item_id' => $item->id, 'name' => $item->name, 'rate' => $item->rate, 'qty' => $qty]);
        }

        $highlight = $price('Engagement Highlight');
        $engagement->items()->create(['section' => ItemSection::Video, 'name' => $highlight->name, 'rate' => $highlight->rate, 'price_item_id' => $highlight->id]);
        $engagement->refreshCachedTotals();
    }

    /**
     * A realistic price list, portfolio and terms for the demo studio.
     */
    private function seedCatalog(Studio $studio): void
    {
        $priceItems = [
            PriceCategory::Crew->value => [
                ['Candid Photographer', 15000, 'Senior'],
                ['Candid Photographer', 12000, 'Mid'],
                ['Traditional Photographer', 7000, null],
                ['Cinematographer', 15000, 'Senior'],
                ['Cinematographer', 12000, 'Mid'],
                ['Traditional Videographer', 8000, null],
                ['Drone Pilot', 10000, null],
                ['Drone Pilot + FPV', 20000, null],
                ['LED Wall with Live (1 screen)', 70000, null],
            ],
            PriceCategory::Video->value => [
                ['Wedding Highlight', 15000, null],
                ['Engagement Highlight', 10000, null],
                ['Wedding Full Film', 7000, null],
                ['Sangeet Full Film', 16000, null],
                ['Mehendi & Haldi Full Film', 6000, null],
                ['All Event Reels', 1500, null],
            ],
            PriceCategory::Photo->value => [
                ['All Couple Photos Edited', 10000, null],
                ['Online Gallery Link', 5000, null],
                ['Face Recognition Link', 5000, null],
            ],
        ];

        foreach ($priceItems as $category => $items) {
            foreach ($items as [$name, $rate, $label]) {
                PriceItem::create([
                    'studio_id' => $studio->id,
                    'category' => $category,
                    'name' => $name,
                    'rate' => $rate,
                    'internal_label' => $label,
                    'note' => match ($name) {
                        'Online Gallery Link' => 'valid for 2 years',
                        'Face Recognition Link' => 'valid for 4 days',
                        default => null,
                    },
                    'delivery' => $category === PriceCategory::Crew->value ? null : '45 days',
                ]);
            }
        }

        foreach (['Wedding', 'Pre-Wedding', 'Engagement'] as $title) {
            PortfolioItem::create([
                'studio_id' => $studio->id,
                'type' => PortfolioType::Album,
                'title' => $title,
                'url' => 'https://example.com/gallery/'.str($title)->slug(),
            ]);
        }

        PortfolioItem::create([
            'studio_id' => $studio->id,
            'type' => PortfolioType::Video,
            'title' => 'Wedding Film',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        foreach (QuotationTemplate::cases() as $template) {
            foreach (TermTemplate::starterTerms($template) as $term) {
                TermTemplate::create([...$term, 'studio_id' => $studio->id, 'template' => $template]);
            }
        }
    }
}
