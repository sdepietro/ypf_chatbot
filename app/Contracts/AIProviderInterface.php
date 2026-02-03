<?php

namespace App\Contracts;

interface AIProviderInterface
{
    /**
     * Send a chat request to the AI provider.
     *
     * @param array $messages Array of messages with 'role' and 'content' keys
     * @param string|null $systemPrompt Optional system prompt to prepend
     * @return array Response with: content, prompt_tokens, completion_tokens, total_tokens, cost, model, provider
     */
    public function chat(array $messages, ?string $systemPrompt = null): array;

    /**
     * Get the provider name (e.g., 'openai', 'deepseek').
     */
    public function getProviderName(): string;

    /**
     * Get the model being used.
     */
    public function getModel(): string;

    /**
     * Check if the provider is properly configured.
     */
    public function isConfigured(): bool;
}
