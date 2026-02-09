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

        $stationContext = 'Background: a fuel pump with vivid cobalt blue (#0451DD) and white details at a clean modern Argentine gas station. Blurred background hints of white columns and blue accents. NO canopy ceiling visible, NO wide shots, NO overhead structures, NO brand names, NO text, NO logos.';

        $fullPrompt = "Hyperrealistic photograph taken with a Canon EOS R5 DSLR, 85mm f/1.4 lens, shallow depth of field with beautiful bokeh, natural daylight, medium close-up shot, eye-level camera. MAIN SUBJECT: {$prompt} The person and the vehicle fill most of the frame. The fuel pump is partially visible next to the car. {$stationContext} CRITICAL: Frame the shot tight on the person and car like a street photography portrait. The gas station is only a blurred backdrop. Style: RAW photo, ultra-detailed skin texture, natural lighting, no CGI, no 3D render, no illustration.";

        try {
            $response = $this->client->images()->create([
                'model' => 'dall-e-3',
                'prompt' => $fullPrompt,
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
