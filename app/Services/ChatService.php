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
            'title' => $agent->name . ' - ' . now()->format('d/m H:i'),
            'status' => 'active',
        ]);

        return $chat->load('agent');
    }

    public function sendMessage(Chat $chat, string $content): array
    {
        // Save human message
        $humanMessage = Message::create([
            'chat_id' => $chat->id,
            'role' => 'human',
            'content' => $content,
        ]);

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

            // Save bot message
            $botMessage = Message::create([
                'chat_id' => $chat->id,
                'role' => 'bot',
                'content' => $response['content'],
                'prompt_tokens' => $response['prompt_tokens'],
                'completion_tokens' => $response['completion_tokens'],
                'cost' => $response['cost'],
                'provider' => $response['provider'],
                'model' => $response['model'],
                'meta' => [
                    'total_tokens' => $response['total_tokens'],
                ],
            ]);

            // Update chat totals
            $chat->addTokensAndCost(
                $response['total_tokens'],
                $response['cost']
            );

            return [
                'human_message' => $humanMessage,
                'bot_message' => $botMessage,
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
