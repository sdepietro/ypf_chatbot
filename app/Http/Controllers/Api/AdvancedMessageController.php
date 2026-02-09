<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Chat;
use App\Services\AdvancedChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvancedMessageController extends Controller
{
    protected AdvancedChatService $advancedChatService;

    public function __construct(AdvancedChatService $advancedChatService)
    {
        $this->advancedChatService = $advancedChatService;
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

        $result = $this->advancedChatService->sendMessage($chat, $request->content, $sttMetadata);

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
            'conversation_ended' => $result['conversation_ended'] ?? false,
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

    public function storeAction(Request $request, int $chatId): JsonResponse
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

        $request->validate([
            'action_type' => 'required|string|in:abrir_tapa,cargar_combustible,cobrar',
            'extra_data' => 'nullable|array',
        ]);

        $message = $this->advancedChatService->saveAction(
            $chat,
            $request->input('action_type'),
            $request->input('extra_data')
        );

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'meta' => $message->meta,
                'created_at' => $message->created_at,
            ],
            'message' => 'Accion registrada',
            'errors' => [],
        ]);
    }
}
