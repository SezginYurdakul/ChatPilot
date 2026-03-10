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
 * Async queue job that stores translated variants in message.translations.
 * It never overwrites the original message text.
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
            $allowedLanguages = config('chatpilot.translation.allowed_languages', []);
            $targetLanguage = strtolower(trim($this->targetLanguage));
            $sourceLanguage = strtolower(trim((string) ($this->message->language ?? '')));

            // Translate only for explicitly supported language pairs.
            if (! in_array($targetLanguage, $allowedLanguages, true)) {
                return;
            }

            // Never auto-detect another language; if source is unknown, keep original.
            if ($sourceLanguage === '' || ! in_array($sourceLanguage, $allowedLanguages, true)) {
                return;
            }

            if ($sourceLanguage === $targetLanguage) {
                return;
            }

            $translator = $this->makeTranslator($targetLanguage);
            $translator->setSource($sourceLanguage);

            $translated = $translator->translate($this->message->text);

            if (! $translated || $translated === $this->message->text) {
                return;
            }

            $translations = $this->message->translations ?? [];
            $translations[$targetLanguage] = $translated;

            $this->message->update([
                'translations' => $translations,
            ]);

            MessageTranslated::dispatch($this->message);
        } catch (\Throwable $e) {
            Log::warning('Message translation failed', [
                'message_id' => $this->message->id,
                'target_language' => $this->targetLanguage,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a translator instance.
     * Extracted for deterministic unit testing.
     */
    protected function makeTranslator(string $targetLanguage): GoogleTranslate
    {
        return new GoogleTranslate($targetLanguage);
    }
}
