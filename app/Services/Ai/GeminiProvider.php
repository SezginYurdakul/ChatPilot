<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Gemini AI provider.
 * Sends chat requests to the Gemini REST API and parses the response.
 */
class GeminiProvider implements AiProviderInterface
{
    public function __construct(
        private string $apiKey,
        private string $model = 'gemini-2.5-flash-lite',
    ) {}

    /**
     * Build the conversation in Gemini's format and call the API.
     * Returns the AI response text along with token usage stats.
     */
    public function chat(string $message, array $history, string $systemPrompt): array
    {
        // Convert our generic history format to Gemini's expected format
        // Gemini uses "user" and "model" roles (not "assistant")
        $contents = [];
        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        // Append the latest visitor message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        $response = Http::timeout(config('chatpilot.ai.timeout'))
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                'system_instruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents' => $contents,
            ]);

        if ($response->failed()) {
            Log::warning('Gemini API call failed', [
                'model' => $this->model,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        $response->throw();
        $data = $response->json();

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $usage = $data['usageMetadata'] ?? [];

        return [
            'text' => $text,
            'prompt_tokens' => $usage['promptTokenCount'] ?? 0,
            'completion_tokens' => $usage['candidatesTokenCount'] ?? 0,
            'model' => $this->model,
        ];
    }
}
