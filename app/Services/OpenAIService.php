<?php

namespace App\Services;

use App\Models\Config;
use OpenAI;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAIService
{
    protected $client;
    protected string $model;
    protected float $temperature;

    // Pricing per 1M tokens (as of 2024 - adjust as needed)
    protected array $pricing = [
        'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
        'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
        'gpt-4-turbo' => ['input' => 10.00, 'output' => 30.00],
        'gpt-4' => ['input' => 30.00, 'output' => 60.00],
        'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50],
    ];

    public function __construct()
    {
        $apiKey = Config::getValue('openai-api-key', env('OPENAI_API_KEY'));
        $this->model = Config::getValue('openai-model', env('OPENAI_MODEL', 'gpt-4o-mini'));
        $this->temperature = (float) Config::getValue('openai-temperature', env('OPENAI_TEMPERATURE', 0.7));

        if (empty($apiKey)) {
            throw new Exception('OpenAI API key not configured');
        }

        $this->client = OpenAI::client($apiKey);
    }

    public function chat(array $messages, ?string $systemPrompt = null): array
    {
        $formattedMessages = [];

        if ($systemPrompt) {
            $formattedMessages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        foreach ($messages as $msg) {
            $role = $msg['role'] === 'human' ? 'user' : ($msg['role'] === 'bot' ? 'assistant' : 'system');
            $formattedMessages[] = [
                'role' => $role,
                'content' => $msg['content'],
            ];
        }

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
            ];
        } catch (Exception $e) {
            Log::error('OpenAI API error', [
                'message' => $e->getMessage(),
                'model' => $this->model,
            ]);
            throw new Exception('Error communicating with OpenAI: ' . $e->getMessage());
        }
    }

    protected function calculateCost(int $promptTokens, int $completionTokens): float
    {
        $modelPricing = $this->pricing[$this->model] ?? $this->pricing['gpt-4o-mini'];

        $inputCost = ($promptTokens / 1_000_000) * $modelPricing['input'];
        $outputCost = ($completionTokens / 1_000_000) * $modelPricing['output'];

        return round($inputCost + $outputCost, 6);
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
