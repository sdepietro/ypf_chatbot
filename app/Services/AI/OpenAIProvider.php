<?php

namespace App\Services\AI;

use OpenAI;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAIProvider extends AbstractAIProvider
{
    protected $client;

    protected array $pricing = [
        'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
        'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
        'gpt-4-turbo' => ['input' => 10.00, 'output' => 30.00],
        'gpt-4' => ['input' => 30.00, 'output' => 60.00],
        'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50],
    ];

    public function __construct()
    {
        $apiKey = $this->getConfig('openai-api-key', 'OPENAI_API_KEY');
        $this->model = $this->getConfig('openai-model', 'OPENAI_MODEL', 'gpt-4o-mini');
        $this->temperature = (float) $this->getConfig('openai-temperature', 'OPENAI_TEMPERATURE', 0.7);

        if (!empty($apiKey)) {
            $this->client = OpenAI::client($apiKey);
        }
    }

    public function chat(array $messages, ?string $systemPrompt = null): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('OpenAI API key not configured');
        }

        $formattedMessages = $this->formatMessages($messages, $systemPrompt);

        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => $formattedMessages,
                'temperature' => $this->temperature,
            ]);

            $content = $response->choices[0]->message->content ?? '';
            $promptTokens = $response->usage->promptTokens ?? 0;
            $completionTokens = $response->usage->completionTokens ?? 0;
            $cost = $this->calculateCost($promptTokens, $completionTokens);

            return [
                'content' => $content,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $promptTokens + $completionTokens,
                'cost' => $cost,
                'model' => $this->model,
                'provider' => $this->getProviderName(),
            ];
        } catch (Exception $e) {
            Log::error('OpenAI API error', [
                'message' => $e->getMessage(),
                'model' => $this->model,
            ]);
            throw new Exception('Error communicating with OpenAI: ' . $e->getMessage());
        }
    }

    public function getProviderName(): string
    {
        return 'openai';
    }

    public function isConfigured(): bool
    {
        return $this->client !== null;
    }
}
