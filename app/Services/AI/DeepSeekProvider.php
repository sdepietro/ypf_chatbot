<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DeepSeekProvider extends AbstractAIProvider
{
    protected ?string $apiKey;
    protected string $baseUrl = 'https://api.deepseek.com';

    protected array $pricing = [
        'deepseek-chat' => ['input' => 0.28, 'output' => 0.42],
        'deepseek-reasoner' => ['input' => 0.55, 'output' => 2.19],
    ];

    public function __construct()
    {
        $this->apiKey = $this->getConfig('deepseek-api-key', 'DEEPSEEK_API_KEY');
        $this->model = $this->getConfig('deepseek-model', 'DEEPSEEK_MODEL', 'deepseek-chat');
        $this->temperature = (float) $this->getConfig('deepseek-temperature', 'DEEPSEEK_TEMPERATURE', 0.7);
    }

    public function chat(array $messages, ?string $systemPrompt = null): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('DeepSeek API key not configured');
        }

        $formattedMessages = $this->formatMessages($messages, $systemPrompt);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => $formattedMessages,
                'temperature' => $this->temperature,
            ]);

            if (!$response->successful()) {
                $error = $response->json('error.message', $response->body());
                throw new Exception('DeepSeek API error: ' . $error);
            }

            $data = $response->json();

            $content = $data['choices'][0]['message']['content'] ?? '';
            $promptTokens = $data['usage']['prompt_tokens'] ?? 0;
            $completionTokens = $data['usage']['completion_tokens'] ?? 0;
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
            Log::error('DeepSeek API error', [
                'message' => $e->getMessage(),
                'model' => $this->model,
            ]);
            throw new Exception('Error communicating with DeepSeek: ' . $e->getMessage());
        }
    }

    public function getProviderName(): string
    {
        return 'deepseek';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
