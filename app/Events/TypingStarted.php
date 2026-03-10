<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when someone starts typing in a conversation.
 * Shows "typing..." indicator to the other party.
 */
class TypingStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $conversationId,
        public string $senderType, // 'visitor', 'admin', 'ai'
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("conversation.{$this->conversationId}");
    }

    public function broadcastWith(): array
    {
        return [
            'sender_type' => $this->senderType,
        ];
    }
}
