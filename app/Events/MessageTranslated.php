<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a message's translation is ready.
 * The widget updates the existing message with the translated text.
 */
class MessageTranslated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("conversation.{$this->message->conversation_id}");
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'translations' => $this->message->translations,
        ];
    }
}
