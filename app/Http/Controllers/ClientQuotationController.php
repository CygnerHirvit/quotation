<?php

namespace App\Http\Controllers;

use App\Enums\ItemSection;
use App\Enums\PortfolioType;
use App\Models\PortfolioItem;
use App\Models\Quotation;
use App\Notifications\QuotationViewed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;

/**
 * The quotation as the client sees it. Public: anyone with the link can open it.
 *
 * Only what the client should see is passed to the view. Rates and internal notes are left out unless the
 * studio turned on the price breakdown, so they never reach the browser.
 */
class ClientQuotationController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $quotation = Quotation::withoutGlobalScope('studio')
            ->with(['studio', 'creator:id,name', 'events.crew', 'items', 'terms'])
            ->where('token', $token)
            ->firstOrFail();

        $studio = $quotation->studio;

        if (! $studio->isActive()) {
            return response()->view('client.unavailable', ['studio' => $studio], 404);
        }

        $isStudioPreview = $request->user()?->studio_id === $studio->id;

        if (! $isStudioPreview && $quotation->markViewed()) {
            Notification::send($quotation->teamToNotify(), new QuotationViewed($quotation));
        }

        $includedItems = $quotation->items->where('included', true);

        $portfolio = PortfolioItem::withoutGlobalScope('studio')
            ->where('studio_id', $studio->id)
            ->where('is_active', true)
            ->ordered()
            ->get()
            ->groupBy(fn (PortfolioItem $item) => $item->type->value);

        return response()->view('client.quotation', [
            'quotation' => $quotation,
            'studio' => $studio,
            'tokens' => $studio->themeTokens(),
            'fonts' => $studio->theme->fonts(),
            'coverUrl' => $studio->coverUrl($quotation->template()->value),
            'videoItems' => $includedItems->where('section', ItemSection::Video)->values(),
            'photoItems' => $includedItems->where('section', ItemSection::Photo)->values(),
            'albums' => $portfolio->get(PortfolioType::Album->value, collect()),
            'videos' => $portfolio->get(PortfolioType::Video->value, collect()),
            'totals' => $quotation->totals(),
            'showBreakdown' => $quotation->show_breakdown,
            'isStudioPreview' => $isStudioPreview,
            'canAccept' => $quotation->canBeAccepted(),
            'signatureImage' => $quotation->signatureDataUri(),
        ])->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
