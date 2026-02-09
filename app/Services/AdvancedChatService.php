<?php

namespace App\Services;

use App\Contracts\AIProviderInterface;
use App\Models\Agent;
use App\Models\Chat;
use App\Models\Message;
use App\Services\AI\DalleService;
use Illuminate\Support\Facades\Log;
use Exception;

class AdvancedChatService
{
    protected AIProviderInterface $aiProvider;
    protected DalleService $dalleService;

    public function __construct(AIProviderInterface $aiProvider, DalleService $dalleService)
    {
        $this->aiProvider = $aiProvider;
        $this->dalleService = $dalleService;
    }

    public function createChat(?int $agentId = null): Chat
    {
        if ($agentId) {
            $agent = Agent::findOrFail($agentId);
        } else {
            $agent = Agent::getRandomActive();
            if (!$agent) {
                throw new Exception('No active agents available');
            }
        }

        $chat = Chat::create([
            'agent_id' => $agent->id,
            'title' => 'Conversacion - ' . now()->format('d/m H:i'),
            'status' => 'active',
            'chat_type' => 'advanced',
        ]);

        $chat->load('agent');

        // Generate scene data + opening messages
        try {
            $this->generateSceneAndOpeningMessages($chat);
        } catch (Exception $e) {
            Log::error('Error generating scene and opening messages', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Generate DALL-E image
        try {
            $this->generateSceneImage($chat);
        } catch (Exception $e) {
            Log::error('Error generating scene image', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);
            // Non-fatal: chat works without image
        }

        return $chat;
    }

    public function generateSceneAndOpeningMessages(Chat $chat): void
    {
        $agentPrompt = $chat->agent->system_prompt;

        $scenePrompt = <<<PROMPT
Eres el director de escena de una simulacion de estacion de servicio YPF.
La personalidad del cliente es:
{$agentPrompt}

Genera un JSON con esta estructura EXACTA (sin markdown ni bloques de codigo):
{
  "vehicle": {
    "brand": "...",
    "model": "...",
    "color": "...",
    "year": 2020,
    "fuel_type": "INFINIA|SUPER|INFINIA_DIESEL|DIESEL_500",
    "fuel_type_label": "Infinia Nafta|Super|Infinia Diesel|Diesel 500"
  },
  "person": {
    "gender": "...",
    "age": 40,
    "appearance": "..."
  },
  "narration": "...",
  "opening_line": "...",
  "image_prompt": "Realistic photo at Argentine YPF gas station..."
}

Reglas:
- vehicle.fuel_type debe ser UNO de: INFINIA, SUPER, INFINIA_DIESEL, DIESEL_500
- fuel_type_label es el nombre legible correspondiente: Infinia Nafta, Super, Infinia Diesel, Diesel 500
- Un auto comun usa nafta (INFINIA o SUPER), una camioneta o vehiculo grande usa diesel (INFINIA_DIESEL o DIESEL_500)
- narration: descripcion breve (2-3 oraciones) en tercera persona como narrador. Vehiculo que llega, persona que baja, pistas sutiles del estado de animo (MOSTRAR, no DECIR)
- opening_line: lo que dice el cliente al playero, en espanol argentino, en personaje
- image_prompt: en INGLES, para generar una imagen realista con DALL-E. Describe la escena en la estacion YPF con el vehiculo y la persona

Responde SOLO con JSON valido.
PROMPT;

        $response = $this->aiProvider->chat([], $scenePrompt);

        $content = trim($response['content']);
        // Strip markdown code block if present
        $content = preg_replace('/^```(?:json)?\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        $parsed = json_decode($content, true);

        if (!$parsed || !isset($parsed['narration']) || !isset($parsed['opening_line'])) {
            Log::warning('Could not parse scene JSON, using fallback', [
                'chat_id' => $chat->id,
                'content' => $content,
            ]);

            // Fallback: save raw content as bot message
            Message::create([
                'chat_id' => $chat->id,
                'role' => 'bot',
                'content' => $content,
                'prompt_tokens' => $response['prompt_tokens'],
                'completion_tokens' => $response['completion_tokens'],
                'cost' => $response['cost'],
                'provider' => $response['provider'],
                'model' => $response['model'],
                'meta' => ['total_tokens' => $response['total_tokens']],
            ]);

            $chat->addLlmCost($response['total_tokens'], $response['cost']);
            return;
        }

        // Save scene_data on chat
        $chat->update(['scene_data' => $parsed]);

        // Save narration as system message
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'system',
            'content' => $parsed['narration'],
        ]);

        // Save opening line as bot message
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'bot',
            'content' => $parsed['opening_line'],
            'prompt_tokens' => $response['prompt_tokens'],
            'completion_tokens' => $response['completion_tokens'],
            'cost' => $response['cost'],
            'provider' => $response['provider'],
            'model' => $response['model'],
            'meta' => ['total_tokens' => $response['total_tokens']],
        ]);

        // Update chat LLM cost
        $chat->addLlmCost($response['total_tokens'], $response['cost']);
    }

    public function generateSceneImage(Chat $chat): void
    {
        $sceneData = $chat->scene_data;
        if (!$sceneData || !isset($sceneData['image_prompt'])) {
            Log::warning('No image_prompt in scene_data, skipping image generation', [
                'chat_id' => $chat->id,
            ]);
            return;
        }

        $savePath = "chat-scenes/{$chat->id}.png";
        $result = $this->dalleService->generateAndSave($sceneData['image_prompt'], $savePath);

        $chat->update(['scene_image_path' => $result['local_path']]);
        $chat->addImageCost($result['cost']);
    }

    public function sendMessage(Chat $chat, string $content, array $sttMetadata = []): array
    {
        // Prepare human message data
        $humanMessageData = [
            'chat_id' => $chat->id,
            'role' => 'human',
            'content' => $content,
        ];

        // Add STT metadata if provided
        if (!empty($sttMetadata)) {
            $humanMessageData = array_merge($humanMessageData, [
                'stt_provider' => $sttMetadata['stt_provider'] ?? null,
                'stt_model' => $sttMetadata['stt_model'] ?? null,
                'stt_duration_ms' => $sttMetadata['stt_duration_ms'] ?? null,
                'stt_cost' => $sttMetadata['stt_cost'] ?? null,
            ]);

            if (isset($sttMetadata['stt_cost']) && $sttMetadata['stt_cost'] > 0) {
                $chat->addSttCost((float) $sttMetadata['stt_cost']);
            }
        }

        $humanMessage = Message::create($humanMessageData);

        // Build history including actions
        $history = $this->buildHistoryWithActions($chat);

        // Get agent system prompt
        $systemPrompt = $chat->agent->system_prompt;

        try {
            $response = $this->aiProvider->chat($history, $systemPrompt);

            // Detect end-of-conversation marker
            $conversationEnded = false;
            $botContent = $response['content'];

            if (str_contains($botContent, '[CONVERSACION_FINALIZADA]')) {
                $botContent = trim(str_replace('[CONVERSACION_FINALIZADA]', '', $botContent));
                $conversationEnded = true;
            }

            $botMessage = Message::create([
                'chat_id' => $chat->id,
                'role' => 'bot',
                'content' => $botContent,
                'prompt_tokens' => $response['prompt_tokens'],
                'completion_tokens' => $response['completion_tokens'],
                'cost' => $response['cost'],
                'provider' => $response['provider'],
                'model' => $response['model'],
                'meta' => ['total_tokens' => $response['total_tokens']],
            ]);

            $chat->addLlmCost($response['total_tokens'], $response['cost']);

            if ($conversationEnded) {
                $chat->finish();
            }

            return [
                'human_message' => $humanMessage,
                'bot_message' => $botMessage,
                'conversation_ended' => $conversationEnded,
                'usage' => [
                    'prompt_tokens' => $response['prompt_tokens'],
                    'completion_tokens' => $response['completion_tokens'],
                    'total_tokens' => $response['total_tokens'],
                    'cost' => $response['cost'],
                    'provider' => $response['provider'],
                    'model' => $response['model'],
                ],
            ];
        } catch (Exception $e) {
            Log::error('AdvancedChatService sendMessage error', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);

            $botMessage = Message::create([
                'chat_id' => $chat->id,
                'role' => 'bot',
                'content' => 'Lo siento, hubo un error al procesar tu mensaje. Por favor intenta de nuevo.',
                'meta' => [
                    'error' => true,
                    'error_message' => $e->getMessage(),
                ],
            ]);

            return [
                'human_message' => $humanMessage,
                'bot_message' => $botMessage,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function saveAction(Chat $chat, string $actionType, ?array $extraData = null): Message
    {
        $actionLabels = [
            'abrir_tapa' => 'Abrio la tapa de combustible',
            'cargar_combustible' => 'Cargo combustible',
            'cobrar' => 'Realizo el cobro',
        ];

        $content = $actionLabels[$actionType] ?? $actionType;

        // Append extra data info if present
        if ($extraData && isset($extraData['fuel_type_label'])) {
            $content .= ' (' . $extraData['fuel_type_label'] . ')';
        }

        return Message::create([
            'chat_id' => $chat->id,
            'role' => 'action',
            'content' => $content,
            'meta' => [
                'action_type' => $actionType,
                'extra_data' => $extraData,
            ],
        ]);
    }

    public function buildHistoryWithActions(Chat $chat): array
    {
        return $chat->messages()
            ->whereIn('role', ['human', 'bot', 'action'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => $msg->role === 'action'
                ? ['role' => 'system', 'content' => "[ACCION DEL PLAYERO] {$msg->content}"]
                : ['role' => $msg->role, 'content' => $msg->content]
            )
            ->toArray();
    }
}
