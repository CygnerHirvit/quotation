@php
    use App\Enums\GstMode;
    use App\Support\Inr;

    $isCorporate = $quotation->template() === \App\Enums\QuotationTemplate::Corporate;
    $preparedBy = $quotation->creator?->name ?? $studio->signature_name;

    $gstNote = match ($quotation->gst_mode) {
        GstMode::Included => 'Including '.(float) $quotation->gst_percent.'% GST',
        GstMode::Extra => 'Exclusive of GST',
        GstMode::None => null,
    };

    $customSections = $customSections ?? collect();

    $sections = ['cover' => 'Cover'];
    foreach ($customSections->where('position', 'after_cover') as $cs) {
        $sections[$cs->anchorId()] = $cs->title;
    }
    $sections['events'] = 'Events';
    foreach ($customSections->where('position', 'after_coverage') as $cs) {
        $sections[$cs->anchorId()] = $cs->title;
    }
    $sections['deliverables'] = 'Deliverables';
    foreach ($customSections->where('position', 'after_deliverables') as $cs) {
        $sections[$cs->anchorId()] = $cs->title;
    }
    $sections['price'] = 'Price';
    foreach ($customSections->where('position', 'after_price') as $cs) {
        $sections[$cs->anchorId()] = $cs->title;
    }
    $sections['terms'] = 'Terms';

    $sectionNum = function (string $key) use ($sections): string {
        $keys = array_keys($sections);
        $idx = array_search($key, $keys, true);
        return sprintf('%02d', ($idx === false ? 0 : $idx) + 1);
    };

    $footerText = collect([$studio->name, $studio->email, $studio->phone, $studio->city])->filter()->join(' · ');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $quotation->displayName() }} · {{ $quotation->number }} · {{ $studio->name }}</title>

    <meta property="og:title" content="{{ $quotation->displayName() }} · {{ $quotation->number }}">
    <meta property="og:description" content="{{ $studio->name }} — {{ $quotation->function_type->label() }}{{ $quotation->datesLabel() ? ' on '.$quotation->datesLabel() : '' }}">
    <meta property="og:url" content="{{ $quotation->publicUrl() }}">
    <meta property="og:type" content="website">
    @if ($studio->logoUrl())
        <meta property="og:image" content="{{ $studio->logoUrl() }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family={{ $fonts['query'] }}|dancing-script:400,600,700&display=swap">

    {{-- Theme values come from the theme enum or a validated #RRGGBB accent, so they are safe to print raw here. --}}
    <style>
        :root {
            @foreach ($tokens as $name => $value)
                --q-{{ $name }}: {!! $value !!};
            @endforeach
            --q-display: {!! $fonts['display'] !!};
            --q-body: {!! $fonts['body'] !!};
            color-scheme: {{ $studio->theme === \App\Enums\StudioTheme::MidnightGold ? 'dark' : 'light' }};
        }

        @page {
            margin: 0;
        }

        @page :left {
            margin: 0;
        }

        @page :right {
            margin: 0;
        }

        @page :first {
            margin: 0;
        }
    </style>

    @vite('resources/css/client.css')
</head>
<body>
    <header class="q-bar">
        <div class="q-wrap">
            <div class="q-bar__row">
                <div class="q-brand">
                    @if ($studio->logoUrl())
                        <img class="q-brand__logo" src="{{ $studio->logoUrl() }}" alt="{{ $studio->name }} logo" onerror="this.replaceWith(Object.assign(document.createElement('span'), {className: 'q-brand__initial', textContent: '{{ addslashes($studio->initial()) }}'}))">
                    @else
                        <span class="q-brand__initial" aria-hidden="true">{{ $studio->initial() }}</span>
                    @endif
                    <div class="q-brand__text">
                        <div class="q-brand__name">{{ $studio->name }}</div>
                        <div class="q-brand__meta">{{ $quotation->displayName() }} · {{ $quotation->number }}</div>
                    </div>
                </div>

                <div class="q-actions">
                    @if ($quotation->isAccepted())
                        <a href="#terms" class="q-btn q-btn--done"><span aria-hidden="true">✓</span><span class="q-btn__text">Accepted</span></a>
                    @elseif ($canAccept)
                        <button type="button" class="q-btn q-btn--primary" data-open-accept><span aria-hidden="true">✍</span>&nbsp;<span class="q-btn__text">Accept &amp; E-Sign</span></button>
                    @endif
                    <a href="{{ $quotation->pdfUrl() }}" class="q-btn" aria-label="Download PDF">
                        <span aria-hidden="true">📥</span><span class="q-btn__text">Download PDF</span>
                    </a>
                    <button type="button" class="q-btn" onclick="window.print()" aria-label="Print or save as PDF">
                        <span aria-hidden="true">⎙</span><span class="q-btn__text">Print</span>
                    </button>
                </div>
            </div>

            <nav class="q-tabs" aria-label="Sections">
                @foreach ($sections as $id => $label)
                    <a href="#{{ $id }}" data-section-link="{{ $id }}" @class(['is-active' => $loop->first])><span>{{ sprintf('%02d', $loop->iteration) }}</span>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="q-wrap">
        @if (session('accepted'))
            <p class="q-notice q-notice--success" role="status">Thank you, {{ $quotation->signed_name }}! Your acceptance has been sent to {{ $studio->name }}. They will contact you about the next steps.</p>
        @endif

        @if ($isStudioPreview)
            <p class="q-notice q-no-print">This is the client view. Opening it while signed in does not mark the quotation as viewed.</p>
        @endif

        @if ($quotation->isExpired())
            <p class="q-notice">This quotation was valid until {{ $quotation->valid_until->format('j M Y') }}. Please contact {{ $studio->name }} to confirm the latest prices.</p>
        @endif

        {{-- Dedicated PDF Cover Page (Page 1 in Print/PDF, hidden on screen) --}}
        <div class="q-pdf-cover">
            <div class="q-pdf-cover__top">
                @if ($studio->logoUrl())
                    <img class="q-pdf-cover__logo" src="{{ $studio->logoUrl() }}" alt="{{ $studio->name }}" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div class="q-pdf-cover__brand-text" style="display: none;">{{ $studio->name }}</div>
                @else
                    <div class="q-pdf-cover__brand-text">{{ $studio->name }}</div>
                @endif
            </div>

            <div class="q-pdf-cover__center">
                <div class="q-pdf-cover__eyebrow">
                    {{ $quotation->bride && $quotation->groom ? 'A PROPOSAL FOR YOUR LOVE STORY' : mb_strtoupper($quotation->documentTitle()) }}
                </div>

                <h1 class="q-pdf-cover__names">
                    @if ($quotation->bride && $quotation->groom)
                        @php
                            $coupleNames = $quotation->coupleNames();
                        @endphp
                        {{ mb_strtoupper($coupleNames[0]) }} <span class="q-amp">&amp;</span> {{ mb_strtoupper($coupleNames[1]) }}
                    @else
                        {{ mb_strtoupper($quotation->displayName()) }}
                    @endif
                </h1>

                <div class="q-pdf-cover__meta">
                    @if ($quotation->datesLabel())
                        <div class="q-pdf-cover__dates">{{ mb_strtoupper($quotation->datesLabel()) }}</div>
                    @endif
                    @php
                        $coverLocation = collect([$quotation->venue, $quotation->city])->filter()->join(', ');
                    @endphp
                    @if ($coverLocation)
                        <div class="q-pdf-cover__location">{{ mb_strtoupper($coverLocation) }}</div>
                    @endif
                </div>
            </div>

            <div class="q-pdf-cover__bottom">
                <div class="q-pdf-cover__heart" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="#FFFFFF"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <div class="q-pdf-cover__tagline">
                    {{ $isCorporate ? ($studio->tagline ?: '...Crafting memories') : '...Love is everything' }}
                </div>
                @if ($footerText)
                    <div class="q-pdf-cover__footer">{{ $footerText }}</div>
                @endif
            </div>
        </div>

        {{-- 01 Cover --}}
        <section id="cover" aria-label="Cover">
            <div @class(['q-cover', 'q-cover--photo' => $coverUrl])>
                @if ($coverUrl)
                    <img class="q-cover__image" src="{{ $coverUrl }}" alt="" onerror="this.closest('.q-cover').classList.remove('q-cover--photo'); this.remove();">
                    <div class="q-cover__shade"></div>
                @endif
                <div class="q-cover__body">
                    <div class="q-eyebrow">{{ $quotation->documentTitle() }}</div>
                    <h1 class="q-cover__names">
                        @if ($quotation->bride && $quotation->groom)
                            @php
                                $coupleNames = $quotation->coupleNames();
                            @endphp
                            {{ $coupleNames[0] }} <span class="q-amp">&amp;</span> {{ $coupleNames[1] }}
                        @else
                            {{ $quotation->displayName() }}
                        @endif
                    </h1>
                    <div class="q-cover__sub">
                        {{ collect([$quotation->datesLabel(), $quotation->city])->filter()->join(' · ') }}
                    </div>
                    @if ($studio->tagline)
                        <div class="q-cover__tagline">{{ $studio->tagline }}</div>
                    @endif
                </div>
            </div>

            <dl class="q-facts">
                <div class="q-fact"><dt>Function</dt><dd>{{ $quotation->function_type->label() }}</dd></div>
                @if ($quotation->function_side)
                    <div class="q-fact"><dt>Side</dt><dd>{{ $quotation->function_side->label() }}</dd></div>
                @endif
                <div class="q-fact"><dt>Dates</dt><dd>{{ $quotation->datesLabel() }}</dd></div>
                <div class="q-fact"><dt>Venue</dt><dd>{{ $quotation->venue ?: '—' }}</dd></div>
                <div class="q-fact"><dt>Quotation no.</dt><dd>{{ $quotation->number }}</dd></div>
                @if ($preparedBy)
                    <div class="q-fact"><dt>Prepared by</dt><dd>{{ $preparedBy }}</dd></div>
                @endif
            </dl>

            @if ($footerText)
                <footer class="q-page-footer q-only-print">{{ $footerText }}</footer>
            @endif
        </section>

        @foreach ($customSections->where('position', 'after_cover') as $customSection)
            @include('client.partials.custom-section', ['customSection' => $customSection])
        @endforeach

        {{-- Events --}}
        <section id="events" class="q-section" aria-labelledby="events-title">
            <div class="q-section__head">
                <div class="q-eyebrow">{{ $sectionNum('events') }} · Coverage</div>
                <h2 id="events-title" class="q-section__title">The functions</h2>
                <p class="q-section__lead">Who will be there for each part of the celebration.</p>
            </div>

            @if ($quotation->events->isEmpty())
                <p class="q-section__lead">Functions will be added soon.</p>
            @else
                <ol class="q-events">
                    @foreach ($quotation->events as $event)
                        <li class="q-event">
                            <div class="q-event__num">{{ sprintf('%02d', $loop->iteration) }}</div>
                            <div>
                                <h3 class="q-event__name">{{ $event->name }}</h3>
                                <div class="q-event__meta">
                                    {{ $event->event_date?->format('l, j M Y') ?? 'Date TBD' }}{{ $event->venue ? ' · '.$event->venue : '' }}
                                </div>
                                @if ($event->crew->isNotEmpty())
                                    <ul class="q-chips" aria-label="Team for {{ $event->name }}">
                                        @foreach ($event->crewSummary() as $member)
                                            <li class="q-chip">@if ($member['qty'] > 1)<b>{{ $member['qty'] }} ×</b> @endif{{ $member['name'] }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif

            @if ($footerText)
                <footer class="q-page-footer q-only-print">{{ $footerText }}</footer>
            @endif
        </section>

        @foreach ($customSections->where('position', 'after_coverage') as $customSection)
            @include('client.partials.custom-section', ['customSection' => $customSection])
        @endforeach

        {{-- Deliverables --}}
        <section id="deliverables" class="q-section" aria-labelledby="deliverables-title">
            <div class="q-section__head">
                <div class="q-eyebrow">{{ $sectionNum('deliverables') }} · Inclusions</div>
                <h2 id="deliverables-title" class="q-section__title">What you receive</h2>
                <p class="q-section__lead">Everything delivered after the celebration.</p>
            </div>

            <div class="q-deliverables">
                @foreach (['Video' => $videoItems, 'Photos' => $photoItems] as $heading => $deliverables)
                    @if ($deliverables->isNotEmpty())
                        <div class="q-card">
                            <h3>{{ $heading }}</h3>
                            <ul class="q-list">
                                @foreach ($deliverables as $item)
                                    <li>
                                        <span class="q-list__name">
                                            @if ($item->qty > 1)<span class="q-list__qty">{{ $item->qty }} ×</span>@endif{{ $item->name }}
                                            @if ($item->note || $item->delivery)
                                                <span class="q-list__note">{{ collect([$item->note, $item->delivery ? 'Delivery in '.$item->delivery : null])->filter()->join(' · ') }}</span>
                                            @endif
                                        </span>
                                        @if ($showBreakdown)
                                            <span class="q-amount">{{ Inr::format($item->qty * $item->rate) }}</span>
                                        @else
                                            <span class="q-badge">Included</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </div>

            @if ($videoItems->isEmpty() && $photoItems->isEmpty())
                <p class="q-section__lead">Deliverables will be added soon.</p>
            @endif

            @if ($albums->isNotEmpty())
                <h3 class="q-subhead">Photo portfolio</h3>
                <div class="q-gallery">
                    @foreach ($albums as $album)
                        <a class="q-tile" href="{{ $album->url }}" target="_blank" rel="noopener noreferrer">
                            @if ($album->coverUrl())
                                <img src="{{ $album->coverUrl() }}" alt="" loading="lazy" onerror="this.remove()">
                            @endif
                            <span class="q-tile__label">{{ $album->title }} ↗</span>
                        </a>
                    @endforeach
                </div>
                <p class="q-hint"><span class="q-no-print">Tap an album to see the full gallery.</span><span class="q-only-print">↗ Click any album to view online</span></p>
            @endif

            @if ($videos->isNotEmpty())
                <h3 class="q-subhead">Video portfolio</h3>
                <div class="q-gallery">
                    @foreach ($videos as $video)
                        <a class="q-tile" href="{{ $video->url }}" target="_blank" rel="noopener noreferrer">
                            @if ($video->coverUrl())
                                <img src="{{ $video->coverUrl() }}" alt="" loading="lazy" onerror="this.remove()">
                            @endif
                            <span class="q-tile__play" aria-hidden="true">▶</span>
                            <span class="q-tile__label">{{ $video->title }}</span>
                        </a>
                    @endforeach
                </div>
                <p class="q-hint"><span class="q-no-print">Tap a video to watch online.</span><span class="q-only-print">▶ Click any video to watch online</span></p>
            @endif

            @if ($footerText)
                <footer class="q-page-footer q-only-print">{{ $footerText }}</footer>
            @endif
        </section>

        @foreach ($customSections->where('position', 'after_deliverables') as $customSection)
            @include('client.partials.custom-section', ['customSection' => $customSection])
        @endforeach

        {{-- Price --}}
        <section id="price" class="q-section" aria-labelledby="price-title">
            <div class="q-section__head">
                <div class="q-eyebrow">{{ $sectionNum('price') }} · Investment</div>
                <h2 id="price-title" class="q-section__title">Price</h2>
            </div>

            <div class="q-price">
                <div class="q-price__label">Grand total</div>
                <div class="q-price__amount">{{ Inr::format($totals->grand) }}</div>
                @if ($gstNote)
                    <div class="q-price__note">{{ $gstNote }}</div>
                @endif
            </div>

            @if ($showBreakdown)
                <div class="q-card q-breakdown">
                    <h3>Breakdown</h3>
                    <dl>
                        @foreach ($quotation->events as $event)
                            @php($eventCost = $event->crew->sum(fn ($crew) => $crew->qty * $crew->rate))
                            @if ($eventCost > 0)
                                <div class="q-row"><dt>{{ $event->name }} team</dt><dd>{{ Inr::format($eventCost) }}</dd></div>
                            @endif
                        @endforeach
                        @if ($totals->video)
                            <div class="q-row"><dt>Video deliverables</dt><dd>{{ Inr::format($totals->video) }}</dd></div>
                        @endif
                        @if ($totals->photo)
                            <div class="q-row"><dt>Photo deliverables</dt><dd>{{ Inr::format($totals->photo) }}</dd></div>
                        @endif
                        <div class="q-row q-row--total"><dt>Subtotal</dt><dd>{{ Inr::format($totals->subtotal) }}</dd></div>
                        @if ($totals->discount)
                            <div class="q-row"><dt>Discount ({{ (float) $quotation->discount_percent }}%)</dt><dd>−{{ Inr::format($totals->discount) }}</dd></div>
                        @endif
                        @if ($totals->gst)
                            <div class="q-row"><dt>GST ({{ (float) $quotation->gst_percent }}%)</dt><dd>{{ Inr::format($totals->gst) }}</dd></div>
                        @endif
                        @if ($totals->isManual && $totals->grand !== $totals->calculated)
                            <div class="q-row"><dt>Special price for you</dt><dd>{{ Inr::format($totals->grand) }}</dd></div>
                        @endif
                    </dl>
                </div>
            @endif

            @if ($footerText)
                <footer class="q-page-footer q-only-print">{{ $footerText }}</footer>
            @endif
        </section>

        @foreach ($customSections->where('position', 'after_price') as $customSection)
            @include('client.partials.custom-section', ['customSection' => $customSection])
        @endforeach

        {{-- Terms --}}
        <section id="terms" class="q-section" aria-labelledby="terms-title">
            <div class="q-section__head">
                <div class="q-eyebrow">{{ $sectionNum('terms') }} · Because Great Stories Begin With Mutual Trust</div>
                <h2 id="terms-title" class="q-section__title">Terms &amp; conditions</h2>
            </div>

            @if ($quotation->terms->isNotEmpty())
                <ol class="q-terms">
                    @foreach ($quotation->terms as $term)
                        <li>
                            <div>
                                @if ($term->title)
                                    <strong>{{ $term->title }}</strong>
                                @endif
                                <p>{{ $term->body }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif

            @if ($quotation->valid_until)
                <p class="q-validity">This quotation is valid until {{ $quotation->valid_until->format('j M Y') }}.</p>
            @endif

            <div class="q-signoff">
                <div class="q-sign">
                    <div class="q-eyebrow">Authorized signature</div>
                    @if ($studio->authorizedSignatureUrl())
                        <div class="q-sign__mark">
                            <img src="{{ $studio->authorizedSignatureUrl() }}" alt="Authorized signature of {{ $studio->signature_name ?: $studio->name }}" onerror="this.closest('.q-sign__mark').remove()">
                        </div>
                    @else
                        <div class="q-sign__line"></div>
                    @endif
                    <div class="q-sign__name">{{ $studio->signature_name ?: $preparedBy ?: $studio->name }}</div>
                    <div class="q-sign__meta">{{ collect([$studio->name, $studio->phone])->filter()->join(' · ') }}</div>
                </div>
                <div @class(['q-sign', 'q-sign--accepted' => $quotation->isAccepted()])>
                    <div class="q-eyebrow">Client acceptance</div>
                    @if ($quotation->isAccepted())
                        <div class="q-sign__mark">
                            @if ($signatureImage)
                                <img src="{{ $signatureImage }}" alt="Signature of {{ $quotation->signed_name }}">
                            @elseif ($quotation->signed_name)
                                <span class="q-sign__typed">{{ $quotation->signed_name }}</span>
                            @endif
                        </div>
                        <div class="q-sign__name">{{ $quotation->signed_name ?: $quotation->displayName() }}</div>
                        <div class="q-sign__meta">Accepted &amp; confirmed{{ $quotation->accepted_at ? ' on '.$quotation->accepted_at->format('j M Y, g:i A') : '' }}</div>
                    @else
                        <div class="q-sign__line"></div>
                        <div class="q-sign__name">{{ $quotation->displayName() }}</div>
                        <div class="q-sign__meta">Signature &amp; date</div>
                        @if ($canAccept)
                            <button type="button" class="q-btn q-btn--primary q-no-print" data-open-accept style="margin-top: 14px">✍ Click here to e-sign &amp; accept</button>
                        @endif
                    @endif
                </div>
            </div>

            @if ($footerText)
                <footer class="q-footer q-page-footer">
                    {{ $footerText }}
                </footer>
            @endif
        </section>
    </main>

    @if ($canAccept)
        @php($acceptErrors = $errors->getBag('accept'))
        <dialog class="q-dialog" id="accept-dialog" aria-labelledby="accept-title" @if ($acceptErrors->any()) data-open-on-load @endif>
            <form method="POST" action="{{ route('quotations.accept', $quotation->token) }}" class="q-dialog__body" id="accept-form">
                @csrf
                <input type="hidden" name="method" value="{{ old('method', 'draw') }}" data-sign-method>
                <input type="hidden" name="signature" data-sign-output>

                <div class="q-dialog__head">
                    <h2 id="accept-title">Accept &amp; sign</h2>
                    <button type="button" class="q-dialog__close" data-close-accept aria-label="Close">×</button>
                </div>

                <p class="q-dialog__lead">
                    You are accepting quotation <strong>{{ $quotation->number }}</strong> from {{ $studio->name }} for
                    <strong>{{ Inr::format($totals->grand) }}</strong>{{ $gstNote ? ' ('.$gstNote.')' : '' }}.
                </p>

                @if ($acceptErrors->any())
                    <div class="q-dialog__errors" role="alert">
                        @foreach ($acceptErrors->all() as $message)
                            <p>{{ $message }}</p>
                        @endforeach
                    </div>
                @endif

                <label class="q-field" for="signed-name">
                    <span>Your full name</span>
                    <input id="signed-name" name="signed_name" type="text" required maxlength="120" autocomplete="name" value="{{ old('signed_name') }}" data-sign-name>
                </label>

                <div class="q-sign-tabs" role="tablist" aria-label="How to sign">
                    <button type="button" role="tab" data-sign-tab="draw">Draw signature</button>
                    <button type="button" role="tab" data-sign-tab="type">Type name</button>
                </div>

                <div data-sign-panel="draw">
                    <canvas class="q-pad" data-sign-pad aria-label="Draw your signature here with your finger or mouse"></canvas>
                    <div class="q-pad__foot">
                        <span>Sign above with your finger or mouse</span>
                        <button type="button" class="q-link" data-sign-clear>Clear</button>
                    </div>
                </div>

                <div data-sign-panel="type" hidden>
                    <div class="q-typed-preview" data-sign-preview>Your name</div>
                    <p class="q-pad__foot"><span>Your typed name will be used as your signature.</span></p>
                </div>

                <label class="q-check" for="agree">
                    <input id="agree" type="checkbox" name="agree" value="1" required @checked(old('agree'))>
                    <span>I have read the quotation and the terms &amp; conditions, and I accept them.</span>
                </label>

                <button type="submit" class="q-btn q-btn--primary q-btn--block">Accept quotation</button>
            </form>
        </dialog>

        <script>
            // Accept dialog with a small signature pad (pointer events, sharp on high-density screens).
            (() => {
                const dialog = document.getElementById('accept-dialog');
                const form = document.getElementById('accept-form');
                const canvas = form.querySelector('[data-sign-pad]');
                const context = canvas.getContext('2d');
                const methodInput = form.querySelector('[data-sign-method]');
                const output = form.querySelector('[data-sign-output]');
                const nameInput = form.querySelector('[data-sign-name]');
                const preview = form.querySelector('[data-sign-preview]');
                let hasInk = false;
                let drawing = false;

                const sizeCanvas = () => {
                    const ratio = window.devicePixelRatio || 1;
                    const width = canvas.clientWidth;
                    const height = canvas.clientHeight;
                    canvas.width = width * ratio;
                    canvas.height = height * ratio;
                    context.setTransform(ratio, 0, 0, ratio, 0, 0);
                    context.lineWidth = 2.4;
                    context.lineCap = 'round';
                    context.lineJoin = 'round';
                    context.strokeStyle = '#111827';
                    hasInk = false;
                };

                const point = (event) => {
                    const box = canvas.getBoundingClientRect();
                    return { x: event.clientX - box.left, y: event.clientY - box.top };
                };

                canvas.addEventListener('pointerdown', (event) => {
                    drawing = true;
                    canvas.setPointerCapture(event.pointerId);
                    const { x, y } = point(event);
                    context.beginPath();
                    context.moveTo(x, y);
                    context.lineTo(x + 0.1, y + 0.1);
                    context.stroke();
                    hasInk = true;
                });
                canvas.addEventListener('pointermove', (event) => {
                    if (!drawing) return;
                    const { x, y } = point(event);
                    context.lineTo(x, y);
                    context.stroke();
                });
                ['pointerup', 'pointercancel', 'pointerleave'].forEach((type) => canvas.addEventListener(type, () => { drawing = false; }));

                form.querySelector('[data-sign-clear]').addEventListener('click', () => {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    hasInk = false;
                });

                const setMethod = (method) => {
                    methodInput.value = method;
                    form.querySelectorAll('[data-sign-tab]').forEach((tab) => tab.setAttribute('aria-selected', tab.dataset.signTab === method ? 'true' : 'false'));
                    form.querySelectorAll('[data-sign-panel]').forEach((panel) => { panel.hidden = panel.dataset.signPanel !== method; });
                    if (method === 'draw') sizeCanvas();
                };
                form.querySelectorAll('[data-sign-tab]').forEach((tab) => tab.addEventListener('click', () => setMethod(tab.dataset.signTab)));

                const updatePreview = () => { preview.textContent = nameInput.value.trim() || 'Your name'; };
                nameInput.addEventListener('input', updatePreview);
                updatePreview();

                form.addEventListener('submit', (event) => {
                    if (methodInput.value === 'draw') {
                        if (!hasInk) {
                            event.preventDefault();
                            alert('Please draw your signature, or choose "Type name".');
                            return;
                        }
                        output.value = canvas.toDataURL('image/png');
                    } else {
                        output.value = '';
                    }
                    form.querySelector('[type=submit]').disabled = true;
                });

                const open = () => {
                    dialog.showModal();
                    setMethod(methodInput.value || 'draw');
                    nameInput.focus();
                };
                document.querySelectorAll('[data-open-accept]').forEach((button) => button.addEventListener('click', open));
                dialog.querySelector('[data-close-accept]').addEventListener('click', () => dialog.close());
                dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
                window.addEventListener('resize', () => {
                    if (dialog.open && methodInput.value === 'draw' && !hasInk) {
                        sizeCanvas();
                    }
                });

                if (dialog.hasAttribute('data-open-on-load')) open();
            })();
        </script>
    @endif

    <script>
        // "Export PDF" from the editor opens this page with ?print=1: wait for fonts and images, then print.
        if (new URLSearchParams(window.location.search).has('print')) {
            window.addEventListener('load', () => {
                (document.fonts ? document.fonts.ready : Promise.resolve()).then(() => setTimeout(() => window.print(), 300));
            });
        }

        // Highlight the section the reader is looking at in the top tabs.
        (() => {
            const links = document.querySelectorAll('[data-section-link]');
            if (!('IntersectionObserver' in window) || links.length === 0) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    links.forEach((link) => link.classList.toggle('is-active', link.dataset.sectionLink === entry.target.id));
                });
            }, { rootMargin: '-40% 0px -55% 0px' });

            links.forEach((link) => {
                const section = document.getElementById(link.dataset.sectionLink);
                if (section) observer.observe(section);
            });
        })();
    </script>
</body>
</html>
