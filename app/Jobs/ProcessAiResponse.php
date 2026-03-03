<?php

namespace App\Jobs;

use App\Events\MessageSent;
use App\Events\TypingStarted;
use App\Models\AiLog;
use App\Models\Conversation;
use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Async queue job that generates an AI response for a conversation.
 * Dispatched when a visitor sends a message and the admin is offline.
 *
 * Flow:
 * 1. Broadcast "AI is typing..." to the widget
 * 2. Build conversation history from recent messages
 * 3. Call the AI provider (Gemini, OpenAI, etc.)
 * 4. Save the AI response as a new message
 * 5. Log token usage for analytics
 * 6. Broadcast the AI message to the widget
 */
class ProcessAiResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public Conversation $conversation,
    ) {}

    /**
     * Execute the job: call AI provider and save the response.
     */
    public function handle(AiService $aiService): void
    {
        $conversation = $this->conversation;
        $site = $conversation->site;

        // Show "AI is typing..." indicator in the widget
        TypingStarted::dispatch($conversation->id, 'ai');

        $systemPrompt = $site->ai_system_prompt
            ?? 'You are a helpful customer support assistant. Be concise and friendly.';

        $history = $aiService->buildHistory($site, $conversation);

        // Get the latest visitor message to send to the AI
        $lastMessage = $conversation->messages()
            ->where('sender_type', 'visitor')
            ->latest('created_at')
            ->first();

        if (! $lastMessage) {
            return;
        }

        $startTime = microtime(true);
        $error = null;

        try {
            $provider = $aiService->resolve($site);
            $result = $provider->chat($lastMessage->text, $history, $systemPrompt);

            // Save the AI response as a message in the conversation
            $aiMessage = $conversation->messages()->create([
                'sender_type' => 'ai',
                'text' => $result['text'],
                'created_at' => now(),
            ]);

            $conversation->update(['last_message_at' => now()]);

            // Broadcast the AI message to the widget in real-time
            MessageSent::dispatch($aiMessage);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $result = ['prompt_tokens' => 0, 'completion_tokens' => 0, 'model' => 'unknown'];

            Log::error('AI response failed', [
                'conversation_id' => $conversation->id,
                'site_id' => $site->id,
                'provider' => $site->ai_provider,
                'error' => $error,
                'attempt' => $this->attempts(),
            ]);

            // Save a fallback message so the visitor isn't left waiting
            $fallbackMessage = $conversation->messages()->create([
                'sender_type' => 'ai',
                'text' => 'Sorry, I am currently unavailable. An admin will respond shortly.',
                'created_at' => now(),
            ]);

            MessageSent::dispatch($fallbackMessage);
        }

        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        // Log AI usage for analytics and cost tracking
        AiLog::create([
            'conversation_id' => $conversation->id,
            'site_id' => $site->id,
            'provider' => $site->ai_provider,
            'model' => $result['model'],
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
            'response_time_ms' => $responseTimeMs,
            'error' => $error,
            'created_at' => now(),
        ]);
    }

    /**
     * Rate limit AI requests to prevent abuse and API cost overruns.
     * Max 10 AI requests per minute per site.
     */
    public function middleware(): array
    {
        return [
            new RateLimited('ai-response'),
        ];
    }
}
