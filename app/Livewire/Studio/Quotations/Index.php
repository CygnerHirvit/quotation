<?php

namespace App\Livewire\Studio\Quotations;

use App\Enums\FunctionType;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Quotations')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $year = '';

    #[Url(except: '')]
    public string $month = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $creator = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Quotation::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'type', 'year', 'month', 'status', 'creator'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'type', 'year', 'month', 'status', 'creator');
        $this->resetPage();
    }

    public function createQuotation(): void
    {
        $this->authorize('create', Quotation::class);

        $quotation = Quotation::createDraft(auth()->user());

        $this->redirectRoute('studio.quotations.edit', $quotation, navigate: true);
    }

    public function duplicate(int $quotationId): void
    {
        $quotation = Quotation::findOrFail($quotationId);

        $this->authorize('update', $quotation);
        $this->authorize('create', Quotation::class);

        $copy = $quotation->duplicate(auth()->user());

        session()->flash('status', "Copied {$quotation->number} to {$copy->number}.");

        $this->redirectRoute('studio.quotations.edit', $copy, navigate: true);
    }

    public function delete(int $quotationId): void
    {
        $quotation = Quotation::findOrFail($quotationId);

        $this->authorize('delete', $quotation);

        $quotation->delete();

        session()->flash('status', "{$quotation->number} deleted.");
    }

    /**
     * @return Builder<Quotation>
     */
    protected function query(): Builder
    {
        return Quotation::query()
            ->with('creator:id,name')
            ->when($this->search !== '', function (Builder $query) {
                $term = '%'.$this->search.'%';

                $query->where(fn (Builder $query) => $query
                    ->where('bride', 'like', $term)
                    ->orWhere('groom', 'like', $term)
                    ->orWhere('client_name', 'like', $term)
                    ->orWhere('number', 'like', $term)
                    ->orWhere('city', 'like', $term));
            })
            ->when(FunctionType::tryFrom($this->type), fn (Builder $query, FunctionType $type) => $query->where('function_type', $type))
            ->when(QuotationStatus::tryFrom($this->status), fn (Builder $query, QuotationStatus $status) => $query->where('status', $status))
            ->when(ctype_digit($this->year), fn (Builder $query) => $query->whereYear('start_date', (int) $this->year))
            ->when(ctype_digit($this->month), fn (Builder $query) => $query->whereMonth('start_date', (int) $this->month))
            ->when(ctype_digit($this->creator), fn (Builder $query) => $query->where('created_by', (int) $this->creator))
            ->latest('id');
    }

    public function render(): View
    {
        $quotations = $this->query()->paginate(20);

        $years = Quotation::query()
            ->whereNotNull('start_date')
            ->toBase()
            ->selectRaw('distinct year(start_date) as year')
            ->pluck('year')
            ->push((int) now()->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        return view('livewire.studio.quotations.index', [
            'quotations' => $quotations,
            'total' => Quotation::count(),
            'types' => FunctionType::cases(),
            'statuses' => QuotationStatus::cases(),
            'years' => $years,
            'creators' => User::where('studio_id', auth()->user()->studio_id)->orderBy('name')->get(['id', 'name']),
            'hasFilters' => collect([$this->search, $this->type, $this->year, $this->month, $this->status, $this->creator])->filter()->isNotEmpty(),
        ]);
    }
}
