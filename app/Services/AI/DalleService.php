<?php

namespace App\Services\AI;

use App\Models\Config;
use Illuminate\Support\Facades\Log;
use OpenAI;
use Exception;

class DalleService
{
    protected $client;
    protected const COST_PER_IMAGE = 0.04; // DALL-E 3 standard 1024x1024

    public function __construct()
    {
        $apiKey = Config::getValue('openai-api-key', env('OPENAI_API_KEY'));

        if (!empty($apiKey)) {
            $this->client = OpenAI::client($apiKey);
        }
    }

    public function generateAndSave(string $prompt, string $savePath): array
    {
        if (!$this->client) {
            throw new Exception('OpenAI API key not configured for DALL-E');
        }

        try {
            $response = $this->client->images()->create([
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
                'quality' => 'standard',
            ]);

            $imageUrl = $response->data[0]->url;

            // Download image from temporary URL
            $imageContent = file_get_contents($imageUrl);
            if ($imageContent === false) {
                throw new Exception('Failed to download generated image');
            }

            // Save directly to public folder
            $fullPath = public_path($savePath);
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents($fullPath, $imageContent);

            Log::info('DALL-E image generated and saved', [
                'path' => $savePath,
                'cost' => self::COST_PER_IMAGE,
            ]);

            return [
                'local_path' => $savePath,
                'cost' => self::COST_PER_IMAGE,
            ];
        } catch (Exception $e) {
            Log::error('DALL-E generation error', [
                'message' => $e->getMessage(),
                'prompt_preview' => substr($prompt, 0, 100),
            ]);
            throw new Exception('Error generating image with DALL-E: ' . $e->getMessage());
        }
    }
}
