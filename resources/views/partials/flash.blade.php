@if (session('status'))
    <div
        role="status"
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition
        class="rounded-xl border border-brand-100 bg-brand-50 px-4 py-3 text-sm font-medium text-brand-700"
    >
        {{ session('status') }}
    </div>
@endif
