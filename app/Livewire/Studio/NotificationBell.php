<?php

namespace App\Livewire\Studio;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Bell in the top bar listing client activity (opened, accepted). Refreshes every minute.
 */
class NotificationBell extends Component
{
    public function open(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        $quotationId = $notification->data['quotation_id'] ?? null;

        if ($quotationId) {
            $this->redirectRoute('studio.quotations.edit', $quotationId, navigate: true);
        }
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.studio.notification-bell', [
            'notifications' => $user->notifications()->latest()->limit(10)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
