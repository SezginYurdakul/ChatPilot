<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a new conversation is created.
 * Notifies the admin panel so it can show the new conversation in the list.
 */
class NewConversation implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
    ) {}

    /**
     * Broadcast on the site's admin channel.
     */
    public function broadcastOn(): Channel
    {
        return new Channel("admin.site.{$this->conversation->site_id}");
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
        ];
    }
}
