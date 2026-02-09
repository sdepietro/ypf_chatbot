<?php

namespace App\Services;

use App\Contracts\AIProviderInterface;
use App\Models\Agent;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Exception;

class ChatService
{
    protected AIProviderInterface $aiProvider;

    public function __construct(AIProviderInterface $aiProvider)
    {
        $this->aiProvider = $aiProvider;
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
        ]);

        $chat->load('agent');

        // Generate narration + first bot message
        try {
            $this->generateOpeningMessages($chat);
        } catch (Exception $e) {
            Log::error('Error generating opening messages', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);
            // Fail silently: chat is created without intro
        }

        return $chat;
    }

    protected function generateOpeningMessages(Chat $chat): void
    {
        $agentPrompt = $chat->agent->system_prompt;

        $introPrompt = <<<PROMPT
Eres el director de escena de una simulacion de atencion en una estacion de servicio YPF.

La personalidad del cliente es:
{$agentPrompt}

Genera DOS cosas:

1. NARRACION: Descripcion breve (2-3 oraciones) en tercera persona, como narrador.
   - Vehiculo que llega (marca, modelo, color inventados)
   - Persona que baja (genero, edad aprox, apariencia)
   - Pistas sutiles del estado de animo (MOSTRAR, no DECIR — no uses palabras como "apurado", "enojado", "indeciso", etc.)

2. PRIMERA_LINEA: Lo que dice el cliente al playero, en espanol argentino, en personaje.

Responde SOLO con JSON valido, sin markdown ni bloques de codigo:
{"narration": "...", "opening_line": "..."}
PROMPT;

        $response = $this->aiProvider->chat([], $introPrompt);

        $content = trim($response['content']);
        $parsed = json_decode($content, true);

        if ($parsed && isset($parsed['narration']) && isset($parsed['opening_line'])) {
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
                'meta' => [
                    'total_tokens' => $response['total_tokens'],
                ],
            ]);
        } else {
            // Fallback: save entire response as bot message
            Log::warning('Could not parse opening messages JSON, using fallback', [
                'chat_id' => $chat->id,
                'content' => $content,
            ]);

            Message::create([
                'chat_id' => $chat->id,
                'role' => 'bot',
                'content' => $content,
                'prompt_tokens' => $response['prompt_tokens'],
                'completion_tokens' => $response['completion_tokens'],
                'cost' => $response['cost'],
                'provider' => $response['provider'],
                'model' => $response['model'],
                'meta' => [
                    'total_tokens' => $response['total_tokens'],
                ],
            ]);
        }

        // Update chat LLM cost
        $chat->addLlmCost(
            $response['total_tokens'],
            $response['cost']
        );
    }

    public function sendMessage(Chat $chat, string $content, array $sttMetadata = []): array
    {
        // Prepare human message data
        $humanMessageData = [
            'chat_id' => $chat->id,
            'role' => 'human',
            'content' => $content,
        ];

        // Add STT metadata if provided (message came from voice)
        if (!empty($sttMetadata)) {
            $humanMessageData = array_merge($humanMessageData, [
                'stt_provider' => $sttMetadata['stt_provider'] ?? null,
                'stt_model' => $sttMetadata['stt_model'] ?? null,
                'stt_duration_ms' => $sttMetadata['stt_duration_ms'] ?? null,
                'stt_cost' => $sttMetadata['stt_cost'] ?? null,
            ]);

            // Update chat STT cost
            if (isset($sttMetadata['stt_cost']) && $sttMetadata['stt_cost'] > 0) {
                $chat->addSttCost((float) $sttMetadata['stt_cost']);
            }
        }

        // Save human message
        $humanMessage = Message::create($humanMessageData);

        // Get chat history for context
        $history = $chat->messages()
            ->whereIn('role', ['human', 'bot'])
            ->get()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        // Get agent system prompt
        $systemPrompt = $chat->agent->system_prompt;

        try {
            // Call AI provider
            $response = $this->aiProvider->chat($history, $systemPrompt);

            // Detect end-of-conversation marker
            $conversationEnded = false;
            $botContent = $response['content'];

            if (str_contains($botContent, '[CONVERSACION_FINALIZADA]')) {
                $botContent = trim(str_replace('[CONVERSACION_FINALIZADA]', '', $botContent));
                $conversationEnded = true;
            }

            // Save bot message
            $botMessage = Message::create([
                'chat_id' => $chat->id,
                'role' => 'bot',
                'content' => $botContent,
                'prompt_tokens' => $response['prompt_tokens'],
                'completion_tokens' => $response['completion_tokens'],
                'cost' => $response['cost'],
                'provider' => $response['provider'],
                'model' => $response['model'],
                'meta' => [
                    'total_tokens' => $response['total_tokens'],
                ],
            ]);

            // Update chat totals (LLM cost)
            $chat->addLlmCost(
                $response['total_tokens'],
                $response['cost']
            );

            // Mark chat as finished if conversation ended
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
            Log::error('ChatService error', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);

            // Save error as bot message
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

    public function getChatHistory(Chat $chat): array
    {
        return $chat->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    public function finishChat(Chat $chat): void
    {
        $chat->finish();
    }
}
