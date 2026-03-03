<?php

namespace App\Services\Ai;

/**
 * Contract that all AI providers must implement.
 * Allows swapping between Gemini, OpenAI, Claude, etc.
 */
interface AiProviderInterface
{
    /**
     * Send a chat message to the AI provider and return the response.
     *
     * @param string $message     The latest visitor message
     * @param array  $history     Previous messages for context [{role, content}, ...]
     * @param string $systemPrompt The AI personality/instructions defined by the site owner
     * @return array{text: string, prompt_tokens: int, completion_tokens: int, model: string}
     */
    public function chat(string $message, array $history, string $systemPrompt): array;
}
