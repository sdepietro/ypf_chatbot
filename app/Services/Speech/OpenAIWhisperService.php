<?php

namespace App\Services\Speech;

use App\Models\Config;
use OpenAI;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAIWhisperService
{
    protected $client;
    protected string $model;

    // Pricing per minute (as of 2024)
    protected array $pricing = [
        'whisper-1' => 0.006, // $0.006 per minute
    ];

    public function __construct()
    {
        $apiKey = Config::getValue('openai-api-key', env('OPENAI_API_KEY'));
        $this->model = Config::getValue('openai-whisper-model', env('OPENAI_WHISPER_MODEL', 'whisper-1'));

        if (empty($apiKey)) {
            throw new Exception('OpenAI API key not configured');
        }

        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Transcribe audio file to text using OpenAI Whisper
     *
     * @param string $audioPath Path to the audio file
     * @param string $language Language code (e.g., 'es' for Spanish)
     * @return array Contains: text, duration_ms, cost, model, provider
     */
    public function transcribe(string $audioPath, string $language = 'es'): array
    {
        try {
            $response = $this->client->audio()->transcribe([
                'model' => $this->model,
                'file' => fopen($audioPath, 'r'),
                'language' => $language,
                'response_format' => 'verbose_json',
            ]);

            $text = $response->text ?? '';
            $durationSeconds = $response->duration ?? 0;
            $durationMs = (int) ($durationSeconds * 1000);
            $cost = $this->calculateCost($durationSeconds);

            Log::info('OpenAI Whisper transcription completed', [
                'model' => $this->model,
                'duration_ms' => $durationMs,
                'text_length' => strlen($text),
                'cost' => $cost,
            ]);

            return [
                'text' => $text,
                'duration_ms' => $durationMs,
                'cost' => $cost,
                'model' => $this->model,
                'provider' => 'openai',
            ];
        } catch (Exception $e) {
            Log::error('OpenAI Whisper transcription error', [
                'message' => $e->getMessage(),
                'model' => $this->model,
            ]);
            throw new Exception('Error transcribing audio with Whisper: ' . $e->getMessage());
        }
    }

    /**
     * Calculate cost based on audio duration
     *
     * @param float $durationSeconds Duration in seconds
     * @return float Cost in USD
     */
    protected function calculateCost(float $durationSeconds): float
    {
        $durationMinutes = $durationSeconds / 60;
        $pricePerMinute = $this->pricing[$this->model] ?? $this->pricing['whisper-1'];

        return round($durationMinutes * $pricePerMinute, 6);
    }

    /**
     * Get the current model being used
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }
}
