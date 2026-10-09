<?php

namespace App\Livewire\Admin\Studios;

use App\Enums\StudioStatus;
use App\Models\Studio;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Studios')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Studio::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $studioId): void
    {
        $studio = Studio::findOrFail($studioId);

        $this->authorize('update', $studio);

        $studio->update([
            'status' => $studio->isActive() ? StudioStatus::Blocked : StudioStatus::Active,
        ]);

        session()->flash('status', $studio->isActive()
            ? "{$studio->name} is active again."
            : "{$studio->name} is blocked. Its users can no longer sign in.");
    }

    public function render(): View
    {
        $studios = Studio::query()
            ->with('admins:id,studio_id,name,email')
            ->withCount('users')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('city', 'like', "%{$this->search}%")
                        ->orWhere('quote_prefix', 'like', "%{$this->search}%");
                });
            })
            ->when(StudioStatus::tryFrom($this->status), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.studios.index', [
            'studios' => $studios,
            'statuses' => StudioStatus::cases(),
        ]);
    }
}
