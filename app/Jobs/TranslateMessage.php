<?php

namespace App\Jobs;

use App\Events\MessageTranslated;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

/**
 * Async queue job that translates an admin message into the visitor's language.
 * Dispatched when an admin sends a message and the visitor speaks a different language.
 */
class TranslateMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 15;

    public function __construct(
        public Message $message,
        public string $targetLanguage,
    ) {}

    public function handle(): void
    {
        try {
            $translator = new GoogleTranslate($this->targetLanguage);
            $translated = $translator->translate($this->message->text);

            // Detect source language from the translator
            $detectedLang = $translator->getLastDetectedSource();

            // Save detected language on the message
            $updates = ['language' => $detectedLang];

            // Skip if source and target language are the same
            if ($detectedLang === $this->targetLanguage) {
                $this->message->update($updates);

                return;
            }

            if (! $translated || $translated === $this->message->text) {
                $this->message->update($updates);

                return;
            }

            $translations = $this->message->translations ?? [];
            $translations[$this->targetLanguage] = $translated;
            $updates['translations'] = $translations;

            $this->message->update($updates);

            MessageTranslated::dispatch($this->message);
        } catch (\Throwable $e) {
            Log::warning('Message translation failed', [
                'message_id' => $this->message->id,
                'target_language' => $this->targetLanguage,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
