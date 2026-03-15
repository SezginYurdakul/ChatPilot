<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a new message is sent in a conversation.
 * Received by both the widget (visitor) and admin panel in real-time.
 */
class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
    ) {}

    /**
     * Broadcast on the conversation's private channel.
     */
    public function broadcastOn(): Channel
    {
        return new Channel("conversation.{$this->message->conversation_id}");
    }

    /**
     * Data sent to the WebSocket client.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'sender_type' => $this->message->sender_type,
                'sender_id' => $this->message->sender_id,
                'text' => $this->message->text,
                'language' => $this->message->language,
                'created_at' => $this->message->created_at?->toISOString(),
            ],
        ];
    }
}
