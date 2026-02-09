<?php

namespace App\Services\Evaluation;

use App\Contracts\AIProviderInterface;
use App\Models\Chat;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Log;
use Exception;

class EvaluationService
{
    protected AIProviderInterface $aiProvider;

    public function __construct(AIProviderInterface $aiProvider)
    {
        $this->aiProvider = $aiProvider;
    }

    public function evaluateChat(Chat $chat): Evaluation
    {
        if ($chat->status !== 'finished') {
            throw new Exception('El chat debe estar finalizado para ser evaluado.');
        }

        if ($chat->isEvaluated()) {
            return $chat->evaluation;
        }

        $chat->load(['messages', 'agent']);

        $history = $this->buildConversationHistory($chat);
        $systemPrompt = $this->buildEvaluationSystemPrompt();
        $userPrompt = $this->buildEvaluationUserPrompt($chat, $history);

        try {
            $response = $this->aiProvider->chat(
                [['role' => 'human', 'content' => $userPrompt]],
                $systemPrompt
            );

            $parsed = $this->parseEvaluationResponse($response['content']);

            $evaluation = Evaluation::create([
                'chat_id' => $chat->id,
                'overall_score' => $parsed['overall_score'],
                'criteria_results' => $parsed['criteria'],
                'overall_feedback' => $parsed['overall_feedback'],
                'prompt_tokens' => $response['prompt_tokens'] ?? null,
                'completion_tokens' => $response['completion_tokens'] ?? null,
                'cost' => $response['cost'] ?? null,
                'provider' => $response['provider'] ?? null,
                'model' => $response['model'] ?? null,
                'meta' => [
                    'raw_response' => $response['content'],
                    'total_tokens' => $response['total_tokens'] ?? null,
                ],
            ]);

            return $evaluation;
        } catch (Exception $e) {
            Log::error('EvaluationService error', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Error al evaluar la conversacion: ' . $e->getMessage());
        }
    }

    protected function buildConversationHistory(Chat $chat): string
    {
        $lines = [];

        foreach ($chat->messages as $message) {
            if ($message->role === 'human') {
                $lines[] = "PLAYERO: {$message->content}";
            } elseif ($message->role === 'bot') {
                $lines[] = "CLIENTE: {$message->content}";
            }
        }

        return implode("\n\n", $lines);
    }

    protected function buildEvaluationSystemPrompt(): string
    {
        return <<<'PROMPT'
Eres un evaluador experto en atencion al cliente en estaciones de servicio YPF Argentina.
Tu tarea es evaluar objetivamente el desempeno del PLAYERO (humano) en la conversacion.
NO evalues al cliente (bot), solo al playero.
Responde UNICAMENTE con JSON valido, sin texto adicional, sin markdown, sin bloques de codigo.
PROMPT;
    }

    protected function buildEvaluationUserPrompt(Chat $chat, string $history): string
    {
        $agentName = $chat->agent->name ?? 'Desconocido';
        $agentDescription = $chat->agent->description ?? 'Sin descripcion';
        $criteriaSection = EvaluationCriteria::buildPromptSection();

        return <<<PROMPT
CONTEXTO:
- Arquetipo del cliente: {$agentName}
- Descripcion: {$agentDescription}

CONVERSACION:
{$history}

CRITERIOS DE EVALUACION:
{$criteriaSection}

Evalua cada criterio con:
- passed: true/false (true si el playero cumplio razonablemente con el criterio)
- score: 0 a 10 (donde 0 es pesimo y 10 es excelente)
- justification: explicacion breve en espanol de por que asignaste ese puntaje

IMPORTANTE:
- Se justo pero exigente. No des puntaje alto si el playero no hizo nada relevante al criterio.
- Si el playero no tuvo oportunidad de demostrar un criterio (por ejemplo, no hubo objeciones del cliente), evalua con score 5 y justifica que no hubo oportunidad.
- El overall_score es un porcentaje de 0 a 100 que refleja el desempeno general.

Formato de respuesta JSON (SOLO JSON, nada mas):
{
  "overall_score": <numero de 0 a 100>,
  "overall_feedback": "<resumen de 2-3 oraciones del desempeno general del playero>",
  "criteria": [
    {"key": "greeting", "passed": true, "score": 8, "justification": "..."},
    {"key": "focus_on_client", "passed": true, "score": 7, "justification": "..."},
    {"key": "persuasion", "passed": false, "score": 3, "justification": "..."},
    {"key": "reciprocity", "passed": false, "score": 2, "justification": "..."},
    {"key": "objections", "passed": true, "score": 5, "justification": "..."},
    {"key": "strategic_questions", "passed": false, "score": 3, "justification": "..."},
    {"key": "cross_selling", "passed": false, "score": 1, "justification": "..."},
    {"key": "upselling", "passed": false, "score": 1, "justification": "..."},
    {"key": "payment_methods", "passed": false, "score": 2, "justification": "..."},
    {"key": "communication_style", "passed": true, "score": 6, "justification": "..."},
    {"key": "discounts_promos", "passed": false, "score": 1, "justification": "..."},
    {"key": "wow_effect", "passed": false, "score": 0, "justification": "..."},
    {"key": "farewell", "passed": true, "score": 7, "justification": "..."}
  ]
}
PROMPT;
    }

    protected function parseEvaluationResponse(string $content): array
    {
        // Clean response: remove markdown code blocks if present
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
        $content = preg_replace('/\s*```\s*$/', '', $content);
        $content = trim($content);

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to parse evaluation JSON', [
                'error' => json_last_error_msg(),
                'content' => substr($content, 0, 500),
            ]);
            throw new Exception('La respuesta del evaluador no es JSON valido: ' . json_last_error_msg());
        }

        if (!isset($decoded['overall_score']) || !isset($decoded['criteria']) || !is_array($decoded['criteria'])) {
            throw new Exception('La respuesta del evaluador no tiene el formato esperado.');
        }

        // Validate and normalize criteria
        $validKeys = EvaluationCriteria::keys();
        $criteria = [];

        foreach ($decoded['criteria'] as $criterion) {
            if (!isset($criterion['key']) || !in_array($criterion['key'], $validKeys)) {
                continue;
            }

            $criteria[] = [
                'key' => $criterion['key'],
                'passed' => (bool) ($criterion['passed'] ?? false),
                'score' => max(0, min(10, (int) ($criterion['score'] ?? 0))),
                'justification' => $criterion['justification'] ?? '',
            ];
        }

        return [
            'overall_score' => max(0, min(100, (float) $decoded['overall_score'])),
            'overall_feedback' => $decoded['overall_feedback'] ?? '',
            'criteria' => $criteria,
        ];
    }
}
