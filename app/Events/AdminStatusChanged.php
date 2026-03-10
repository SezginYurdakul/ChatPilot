<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when an admin goes online or offline.
 * Widget uses this to decide whether to show "AI will respond" message.
 */
class AdminStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $siteId,
        public bool $online,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("admin.site.{$this->siteId}");
    }

    public function broadcastWith(): array
    {
        return [
            'online' => $this->online,
        ];
    }
}
