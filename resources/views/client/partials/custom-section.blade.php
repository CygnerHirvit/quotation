<section id="{{ $customSection->anchorId() }}" class="q-section q-custom-section" aria-labelledby="{{ $customSection->anchorId() }}-title">
    <div class="q-section__head">
        <div class="q-eyebrow">{{ $sectionNum($customSection->anchorId()) }} · {{ mb_strtoupper($customSection->title) }}</div>
        <h2 id="{{ $customSection->anchorId() }}-title" class="q-section__title">{{ $customSection->title }}</h2>
    </div>

    @if ($customSection->tagline)
        <div class="q-scope-banner">
            {{ $customSection->tagline }}
        </div>
    @endif

    <div class="q-scope-items">
        @foreach ($customSection->items as $item)
            <div class="q-scope-item">
                <h3 class="q-scope-item__title">{{ $item->title }}</h3>
                <p class="q-scope-item__desc">{{ $item->description }}</p>
            </div>
        @endforeach
    </div>

    @if ($footerText)
        <footer class="q-page-footer q-only-print">{{ $footerText }}</footer>
    @endif
</section>

