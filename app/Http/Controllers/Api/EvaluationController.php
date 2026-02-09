<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Services\Evaluation\EvaluationService;
use Illuminate\Http\JsonResponse;

class EvaluationController extends Controller
{
    protected EvaluationService $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function evaluate(int $chatId): JsonResponse
    {
        $chat = Chat::with('agent')->find($chatId);

        if (!$chat) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Chat no encontrado',
                'errors' => [],
            ], 404);
        }

        if ($chat->status !== 'finished') {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'El chat debe estar finalizado para ser evaluado',
                'errors' => [],
            ], 400);
        }

        if ($chat->isEvaluated()) {
            $evaluation = $chat->evaluation;

            return response()->json([
                'status' => true,
                'data' => $this->formatEvaluation($evaluation),
                'message' => 'Evaluacion ya existente',
                'errors' => [],
            ]);
        }

        try {
            $evaluation = $this->evaluationService->evaluateChat($chat);

            return response()->json([
                'status' => true,
                'data' => $this->formatEvaluation($evaluation),
                'message' => 'Evaluacion completada',
                'errors' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => $e->getMessage(),
                'errors' => [],
            ], 500);
        }
    }

    public function show(int $chatId): JsonResponse
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

        $evaluation = $chat->evaluation;

        if (!$evaluation) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Este chat no tiene evaluacion',
                'errors' => [],
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $this->formatEvaluation($evaluation),
            'message' => 'OK',
            'errors' => [],
        ]);
    }

    protected function formatEvaluation($evaluation): array
    {
        return [
            'id' => $evaluation->id,
            'chat_id' => $evaluation->chat_id,
            'overall_score' => (float) $evaluation->overall_score,
            'overall_feedback' => $evaluation->overall_feedback,
            'criteria_results' => $evaluation->criteria_results,
            'passed' => $evaluation->isPassed(),
            'prompt_tokens' => $evaluation->prompt_tokens,
            'completion_tokens' => $evaluation->completion_tokens,
            'total_tokens' => ($evaluation->prompt_tokens ?? 0) + ($evaluation->completion_tokens ?? 0),
            'provider' => $evaluation->provider,
            'model' => $evaluation->model,
            'cost' => $evaluation->cost,
            'created_at' => $evaluation->created_at,
        ];
    }
}
