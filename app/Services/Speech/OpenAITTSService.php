<?php

namespace App\Services\Speech;

use App\Models\Config;
use OpenAI;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAITTSService
{
    protected $client;
    protected string $model;
    protected string $voice;

    // Available voices
    public const VOICES = ['alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer'];

    // Pricing per 1M characters (as of 2024)
    protected array $pricing = [
        'tts-1' => 15.00,    // $15.00 per 1M characters
        'tts-1-hd' => 30.00, // $30.00 per 1M characters
    ];

    public function __construct()
    {
        $apiKey = Config::getValue('openai-api-key', env('OPENAI_API_KEY'));
        $this->model = Config::getValue('openai-tts-model', env('OPENAI_TTS_MODEL', 'tts-1'));
        $this->voice = Config::getValue('openai-tts-voice', env('OPENAI_TTS_VOICE', 'alloy'));

        if (empty($apiKey)) {
            throw new Exception('OpenAI API key not configured');
        }

        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Synthesize text to speech using OpenAI TTS
     *
     * @param string $text Text to convert to speech
     * @param string|null $voice Voice to use (alloy, echo, fable, onyx, nova, shimmer)
     * @return array Contains: audio_base64, content_type, characters, cost, model, voice, provider
     */
    public function synthesize(string $text, ?string $voice = null): array
    {
        $voice = $voice ?? $this->voice;

        // Validate voice
        if (!in_array($voice, self::VOICES)) {
            $voice = 'alloy';
        }

        try {
            $response = $this->client->audio()->speech([
                'model' => $this->model,
                'input' => $text,
                'voice' => $voice,
                'response_format' => 'mp3',
            ]);

            $audioContent = $response;
            $audioBase64 = base64_encode($audioContent);
            $characters = mb_strlen($text);
            $cost = $this->calculateCost($characters);

            Log::info('OpenAI TTS synthesis completed', [
                'model' => $this->model,
                'voice' => $voice,
                'characters' => $characters,
                'audio_size' => strlen($audioContent),
                'cost' => $cost,
            ]);

            return [
                'audio_base64' => $audioBase64,
                'content_type' => 'audio/mpeg',
                'characters' => $characters,
                'cost' => $cost,
                'model' => $this->model,
                'voice' => $voice,
                'provider' => 'openai',
            ];
        } catch (Exception $e) {
            Log::error('OpenAI TTS synthesis error', [
                'message' => $e->getMessage(),
                'model' => $this->model,
                'voice' => $voice,
            ]);
            throw new Exception('Error synthesizing speech with OpenAI TTS: ' . $e->getMessage());
        }
    }

    /**
     * Calculate cost based on character count
     *
     * @param int $characters Number of characters
     * @return float Cost in USD
     */
    protected function calculateCost(int $characters): float
    {
        $pricePerMillion = $this->pricing[$this->model] ?? $this->pricing['tts-1'];

        return round(($characters / 1_000_000) * $pricePerMillion, 6);
    }

    /**
     * Get available voices
     *
     * @return array
     */
    public function getVoices(): array
    {
        return array_map(function ($voice) {
            return [
                'id' => $voice,
                'name' => ucfirst($voice),
                'provider' => 'openai',
            ];
        }, self::VOICES);
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

    /**
     * Get the current voice being used
     *
     * @return string
     */
    public function getVoice(): string
    {
        return $this->voice;
    }
}
