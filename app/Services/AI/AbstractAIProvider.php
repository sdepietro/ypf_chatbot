<?php

namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use App\Models\Config;

abstract class AbstractAIProvider implements AIProviderInterface
{
    protected string $model;
    protected float $temperature;
    protected array $pricing = [];

    /**
     * Format messages for the AI API.
     * Converts internal 'human'/'bot' roles to 'user'/'assistant'.
     */
    protected function formatMessages(array $messages, ?string $systemPrompt = null): array
    {
        $formattedMessages = [];

        if ($systemPrompt) {
            $formattedMessages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        foreach ($messages as $msg) {
            $role = match ($msg['role']) {
                'human' => 'user',
                'bot' => 'assistant',
                default => 'system',
            };

            $formattedMessages[] = [
                'role' => $role,
                'content' => $msg['content'],
            ];
        }

        return $formattedMessages;
    }

    /**
     * Calculate the cost based on token usage.
     */
    protected function calculateCost(int $promptTokens, int $completionTokens): float
    {
        $modelPricing = $this->pricing[$this->model] ?? array_values($this->pricing)[0] ?? ['input' => 0, 'output' => 0];

        $inputCost = ($promptTokens / 1_000_000) * $modelPricing['input'];
        $outputCost = ($completionTokens / 1_000_000) * $modelPricing['output'];

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Get a config value from the database or environment.
     */
    protected function getConfig(string $tag, $envKey = null, $default = null)
    {
        return Config::getValue($tag, $envKey ? env($envKey, $default) : $default);
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
