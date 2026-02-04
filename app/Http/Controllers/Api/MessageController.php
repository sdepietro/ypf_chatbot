<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Chat;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(int $chatId): JsonResponse
    {
        $chat = Chat::find($chatId);

        if (!$chat) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Chat no encontrado',
                'errors' => [],
            ], 404);
        }

        $messages = $chat->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'prompt_tokens' => $msg->prompt_tokens,
                'completion_tokens' => $msg->completion_tokens,
                'cost' => $msg->cost,
                'created_at' => $msg->created_at,
            ]);

        return response()->json([
            'status' => true,
            'data' => $messages,
            'message' => 'OK',
            'errors' => [],
        ]);
    }

    public function store(SendMessageRequest $request, int $chatId): JsonResponse
    {
        $chat = Chat::find($chatId);

        if (!$chat) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Chat no encontrado',
                'errors' => [],
            ], 404);
        }

        if ($chat->status === 'finished') {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Este chat ya ha finalizado',
                'errors' => [],
            ], 400);
        }

        // Collect STT metadata if provided
        $sttMetadata = [];
        if ($request->has('stt_cost') || $request->has('stt_provider')) {
            $sttMetadata = [
                'stt_provider' => $request->input('stt_provider'),
                'stt_model' => $request->input('stt_model'),
                'stt_duration_ms' => $request->input('stt_duration_ms'),
                'stt_cost' => $request->input('stt_cost'),
            ];
        }

        $result = $this->chatService->sendMessage($chat, $request->content, $sttMetadata);

        $response = [
            'human_message' => [
                'id' => $result['human_message']->id,
                'role' => $result['human_message']->role,
                'content' => $result['human_message']->content,
                'created_at' => $result['human_message']->created_at,
            ],
            'bot_message' => [
                'id' => $result['bot_message']->id,
                'role' => $result['bot_message']->role,
                'content' => $result['bot_message']->content,
                'prompt_tokens' => $result['bot_message']->prompt_tokens,
                'completion_tokens' => $result['bot_message']->completion_tokens,
                'cost' => $result['bot_message']->cost,
                'created_at' => $result['bot_message']->created_at,
            ],
        ];

        if (isset($result['usage'])) {
            $response['usage'] = $result['usage'];
        }

        return response()->json([
            'status' => true,
            'data' => $response,
            'message' => isset($result['error']) ? 'Error al procesar mensaje' : 'Mensaje enviado',
            'errors' => isset($result['error']) ? ['openai' => $result['error']] : [],
        ]);
    }
}
