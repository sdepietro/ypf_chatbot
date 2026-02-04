<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranscribeAudioRequest;
use App\Http\Requests\SynthesizeSpeechRequest;
use App\Models\Chat;
use App\Models\Message;
use App\Services\Speech\OpenAIWhisperService;
use App\Services\Speech\OpenAITTSService;
use Illuminate\Support\Facades\Log;
use Exception;

class SpeechController extends Controller
{
    /**
     * Transcribe audio to text using OpenAI Whisper
     *
     * POST /api/speech/transcribe
     */
    public function transcribe(TranscribeAudioRequest $request)
    {
        $tempFile = null;

        try {
            $audioFile = $request->file('audio');
            $language = $request->input('language', 'es');

            // Get the original extension (OpenAI needs it to detect format)
            $extension = $audioFile->getClientOriginalExtension() ?: 'webm';

            // Copy to temp file with proper extension
            $tempFile = sys_get_temp_dir() . '/' . uniqid('whisper_') . '.' . $extension;
            copy($audioFile->getRealPath(), $tempFile);

            $whisperService = new OpenAIWhisperService();
            $result = $whisperService->transcribe($tempFile, $language);

            // Clean up
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'text' => $result['text'],
                    'stt_provider' => $result['provider'],
                    'stt_model' => $result['model'],
                    'stt_duration_ms' => $result['duration_ms'],
                    'stt_cost' => $result['cost'],
                ],
                'message' => 'Audio transcrito exitosamente',
                'errors' => [],
            ]);
        } catch (Exception $e) {
            // Clean up on error
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }

            Log::error('Speech transcription failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Error al transcribir el audio: ' . $e->getMessage(),
                'errors' => [],
            ], 500);
        }
    }

    /**
     * Synthesize text to speech using OpenAI TTS
     *
     * POST /api/speech/synthesize
     */
    public function synthesize(SynthesizeSpeechRequest $request)
    {
        try {
            $text = $request->input('text');
            $voice = $request->input('voice');
            $messageId = $request->input('message_id');

            $ttsService = new OpenAITTSService();
            $result = $ttsService->synthesize($text, $voice);

            // Update message with TTS metadata if message_id provided
            if ($messageId) {
                $message = Message::find($messageId);
                if ($message) {
                    $message->update([
                        'tts_provider' => $result['provider'],
                        'tts_model' => $result['model'],
                        'tts_voice' => $result['voice'],
                        'tts_characters' => $result['characters'],
                        'tts_cost' => $result['cost'],
                    ]);

                    // Update chat TTS cost
                    if ($message->chat_id && $result['cost'] > 0) {
                        $chat = Chat::find($message->chat_id);
                        if ($chat) {
                            $chat->addTtsCost((float) $result['cost']);
                        }
                    }
                }
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'audio_base64' => $result['audio_base64'],
                    'content_type' => $result['content_type'],
                    'tts_provider' => $result['provider'],
                    'tts_model' => $result['model'],
                    'tts_voice' => $result['voice'],
                    'tts_characters' => $result['characters'],
                    'tts_cost' => $result['cost'],
                ],
                'message' => 'Texto sintetizado exitosamente',
                'errors' => [],
            ]);
        } catch (Exception $e) {
            Log::error('Speech synthesis failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Error al sintetizar el audio: ' . $e->getMessage(),
                'errors' => [],
            ], 500);
        }
    }

    /**
     * Get available TTS voices
     *
     * GET /api/speech/voices
     */
    public function voices()
    {
        try {
            $ttsService = new OpenAITTSService();
            $voices = $ttsService->getVoices();

            return response()->json([
                'status' => true,
                'data' => [
                    'voices' => $voices,
                    'default_voice' => $ttsService->getVoice(),
                    'model' => $ttsService->getModel(),
                ],
                'message' => 'Voces obtenidas exitosamente',
                'errors' => [],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get TTS voices', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Error al obtener las voces: ' . $e->getMessage(),
                'errors' => [],
            ], 500);
        }
    }
}
