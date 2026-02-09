<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Services\AdvancedChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvancedChatController extends Controller
{
    protected AdvancedChatService $advancedChatService;

    public function __construct(AdvancedChatService $advancedChatService)
    {
        $this->advancedChatService = $advancedChatService;
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $agentId = $request->input('agent_id');
            $chat = $this->advancedChatService->createChat($agentId);
            $chat->refresh();

            return response()->json([
                'status' => true,
                'data' => [
                    'id' => $chat->id,
                    'title' => $chat->title,
                    'status' => $chat->status,
                    'chat_type' => $chat->chat_type,
                    'agent' => [
                        'id' => $chat->agent->id,
                        'name' => $chat->agent->name,
                    ],
                    'scene_data' => $chat->scene_data,
                    'scene_image_url' => $chat->scene_image_url,
                    'total_tokens' => $chat->total_tokens,
                    'total_cost' => $chat->total_cost,
                    'total_llm_cost' => $chat->total_llm_cost,
                    'total_tts_cost' => $chat->total_tts_cost,
                    'total_stt_cost' => $chat->total_stt_cost,
                    'total_image_cost' => $chat->total_image_cost,
                    'created_at' => $chat->created_at,
                    'updated_at' => $chat->updated_at,
                ],
                'message' => 'Chat advanced creado exitosamente',
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
        $chat = Chat::with(['agent:id,name,description', 'evaluation:id,chat_id,overall_score,cost,prompt_tokens,completion_tokens'])->find($id);

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
                'chat_type' => $chat->chat_type,
                'agent' => $chat->agent ? [
                    'id' => $chat->agent->id,
                    'name' => $chat->agent->name,
                    'description' => $chat->agent->description,
                ] : null,
                'scene_data' => $chat->scene_data,
                'scene_image_url' => $chat->scene_image_url,
                'total_tokens' => $chat->total_tokens,
                'total_cost' => $chat->total_cost,
                'total_llm_cost' => $chat->total_llm_cost,
                'total_tts_cost' => $chat->total_tts_cost,
                'total_stt_cost' => $chat->total_stt_cost,
                'total_image_cost' => $chat->total_image_cost,
                'evaluation' => $chat->evaluation ? [
                    'overall_score' => (float) $chat->evaluation->overall_score,
                    'passed' => $chat->evaluation->isPassed(),
                    'cost' => $chat->evaluation->cost,
                    'tokens' => ($chat->evaluation->prompt_tokens ?? 0) + ($chat->evaluation->completion_tokens ?? 0),
                ] : null,
                'created_at' => $chat->created_at,
                'updated_at' => $chat->updated_at,
            ],
            'message' => 'OK',
            'errors' => [],
        ]);
    }
}
