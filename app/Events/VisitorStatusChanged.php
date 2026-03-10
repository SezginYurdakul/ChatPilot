<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a visitor goes online or offline.
 * Admin panel uses this to show visitor presence indicator.
 */
class VisitorStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $conversationId,
        public bool $online,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("conversation.{$this->conversationId}");
    }

    public function broadcastWith(): array
    {
        return [
            'visitor_online' => $this->online,
        ];
    }
}
