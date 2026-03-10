<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI provider (GPT-4o, GPT-4o-mini, etc.).
 * Sends chat requests to the OpenAI Chat Completions API.
 */
class OpenAiProvider implements AiProviderInterface
{
    public function __construct(
        private string $apiKey,
        private string $model = 'gpt-4o-mini',
    ) {}

    /**
     * Build the conversation in OpenAI's format and call the API.
     * OpenAI uses "system", "user", "assistant" roles.
     */
    public function chat(string $message, array $history, string $systemPrompt): array
    {
        // OpenAI message format: array of {role, content} objects
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::timeout(config('chatpilot.ai.timeout'))
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
            ]);

        if ($response->failed()) {
            $responseBody = $response->body();
            Log::warning('OpenAI API call failed', [
                'model' => $this->model,
                'status' => $response->status(),
                'body_hash' => hash('sha256', $responseBody),
                'body_length' => strlen($responseBody),
            ]);
        }

        $response->throw();
        $data = $response->json();

        return [
            'text' => $data['choices'][0]['message']['content'] ?? '',
            'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
            'model' => $this->model,
        ];
    }
}
