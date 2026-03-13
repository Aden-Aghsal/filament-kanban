<?php

namespace App\Jobs;

use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFilamentDatabaseNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $notificationData
     * @param  array<int>  $userIds
     */
    public function __construct(
        public array $notificationData,
        public array $userIds,
        public bool $dispatchEvent = false,
    ) {
    }

    public function handle(): void
    {
        if (empty($this->userIds)) {
            return;
        }

        $users = User::query()
            ->whereIn('id', $this->userIds)
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        FilamentNotification::fromArray($this->notificationData)
            ->sendToDatabase($users, isEventDispatched: $this->dispatchEvent);
    }
}
