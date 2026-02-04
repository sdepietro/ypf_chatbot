<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function index(): JsonResponse
    {
        $chats = Chat::with('agent:id,name')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($chat) => [
                'id' => $chat->id,
                'title' => $chat->title,
                'status' => $chat->status,
                'agent' => $chat->agent ? [
                    'id' => $chat->agent->id,
                    'name' => $chat->agent->name,
                ] : null,
                'total_tokens' => $chat->total_tokens,
                'total_cost' => $chat->total_cost,
                'total_llm_cost' => $chat->total_llm_cost,
                'total_tts_cost' => $chat->total_tts_cost,
                'total_stt_cost' => $chat->total_stt_cost,
                'created_at' => $chat->created_at,
                'updated_at' => $chat->updated_at,
            ]);

        return response()->json([
            'status' => true,
            'data' => $chats,
            'message' => 'OK',
            'errors' => [],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $agentId = $request->input('agent_id');
            $chat = $this->chatService->createChat($agentId);

            return response()->json([
                'status' => true,
                'data' => [
                    'id' => $chat->id,
                    'title' => $chat->title,
                    'status' => $chat->status,
                    'agent' => [
                        'id' => $chat->agent->id,
                        'name' => $chat->agent->name,
                    ],
                    'total_tokens' => $chat->total_tokens,
                    'total_cost' => $chat->total_cost,
                    'total_llm_cost' => $chat->total_llm_cost,
                    'total_tts_cost' => $chat->total_tts_cost,
                    'total_stt_cost' => $chat->total_stt_cost,
                    'created_at' => $chat->created_at,
                    'updated_at' => $chat->updated_at,
                ],
                'message' => 'Chat creado exitosamente',
                'errors' => [],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $e->getMessage(),
                'errors' => [],
            ], 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $chat = Chat::with('agent:id,name,description')->find($id);

        if (!$chat) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Chat no encontrado',
                'errors' => [],
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $chat->id,
                'title' => $chat->title,
                'status' => $chat->status,
                'agent' => $chat->agent ? [
                    'id' => $chat->agent->id,
                    'name' => $chat->agent->name,
                    'description' => $chat->agent->description,
                ] : null,
                'total_tokens' => $chat->total_tokens,
                'total_cost' => $chat->total_cost,
                'total_llm_cost' => $chat->total_llm_cost,
                'total_tts_cost' => $chat->total_tts_cost,
                'total_stt_cost' => $chat->total_stt_cost,
                'created_at' => $chat->created_at,
                'updated_at' => $chat->updated_at,
            ],
            'message' => 'OK',
            'errors' => [],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Chat no encontrado',
                'errors' => [],
            ], 404);
        }

        $chat->delete();

        return response()->json([
            'status' => true,
            'data' => null,
            'message' => 'Chat eliminado exitosamente',
            'errors' => [],
        ]);
    }
}
