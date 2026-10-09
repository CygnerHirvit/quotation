<?php

namespace App\Livewire\Admin;

use App\Enums\StudioStatus;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Platform overview')]
class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.admin.dashboard', [
            'studioCount' => Studio::count(),
            'activeStudioCount' => Studio::where('status', StudioStatus::Active)->count(),
            'userCount' => User::whereNotNull('studio_id')->count(),
            'recentStudios' => Studio::withCount('users')->latest()->limit(5)->get(),
        ]);
    }
}
