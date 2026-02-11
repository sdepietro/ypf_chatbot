<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Chat;
use App\Models\Evaluation;
use App\Models\Message;
use App\Services\Evaluation\EvaluationCriteria;
use Illuminate\Database\Eloquent\Builder;

class DashboardService
{
    public function getKpis(?string $from, ?string $to): array
    {
        $chatsQuery = Chat::query();
        $this->applyDateFilter($chatsQuery, $from, $to);

        $totalChats = (clone $chatsQuery)->count();
        $finished = (clone $chatsQuery)->where('status', 'finished')->count();
        $active = (clone $chatsQuery)->where('status', 'active')->count();
        $totalCost = (clone $chatsQuery)->sum('total_cost');
        $totalTokens = (clone $chatsQuery)->sum('total_tokens');

        $evalsQuery = Evaluation::query();
        $this->applyDateFilterViaChat($evalsQuery, $from, $to);

        $avgScore = $evalsQuery->avg('overall_score');
        $totalEvals = (clone $evalsQuery)->count();
        $passedEvals = (clone $evalsQuery)->whereRaw('overall_score >= 70')->count();
        $passRate = $totalEvals > 0 ? round(($passedEvals / $totalEvals) * 100, 1) : 0;

        return [
            'total_chats' => $totalChats,
            'finished' => $finished,
            'active' => $active,
            'total_cost' => round((float) $totalCost, 4),
            'avg_score' => round((float) ($avgScore ?? 0), 1),
            'pass_rate' => $passRate,
            'total_tokens' => (int) $totalTokens,
        ];
    }

    public function getCostsByServiceType(?string $from, ?string $to): array
    {
        $query = Chat::query();
        $this->applyDateFilter($query, $from, $to);

        $llm = (clone $query)->sum('total_llm_cost');
        $tts = (clone $query)->sum('total_tts_cost');
        $stt = (clone $query)->sum('total_stt_cost');
        $image = (clone $query)->sum('total_image_cost');

        $evalQuery = Evaluation::query();
        $this->applyDateFilterViaChat($evalQuery, $from, $to);
        $evaluation = $evalQuery->sum('cost');

        return [
            'llm' => round((float) $llm, 4),
            'tts' => round((float) $tts, 4),
            'stt' => round((float) $stt, 4),
            'image' => round((float) $image, 4),
            'evaluation' => round((float) $evaluation, 4),
        ];
    }

    public function getCostsByProvider(?string $from, ?string $to): array
    {
        $msgQuery = Message::query()->whereNotNull('provider');
        $this->applyDateFilterViaChat($msgQuery, $from, $to);

        $messageCosts = $msgQuery
            ->selectRaw('provider, SUM(cost) as cost')
            ->groupBy('provider')
            ->get()
            ->keyBy('provider')
            ->toArray();

        $evalQuery = Evaluation::query()->whereNotNull('provider');
        $this->applyDateFilterViaChat($evalQuery, $from, $to);

        $evalCosts = $evalQuery
            ->selectRaw('provider, SUM(cost) as cost')
            ->groupBy('provider')
            ->get()
            ->keyBy('provider')
            ->toArray();

        $providers = array_unique(array_merge(array_keys($messageCosts), array_keys($evalCosts)));
        $result = [];

        foreach ($providers as $provider) {
            $msgCost = (float) ($messageCosts[$provider]['cost'] ?? 0);
            $evalCost = (float) ($evalCosts[$provider]['cost'] ?? 0);
            $result[] = [
                'provider' => $provider,
                'cost' => round($msgCost + $evalCost, 4),
            ];
        }

        usort($result, fn($a, $b) => $b['cost'] <=> $a['cost']);

        return $result;
    }

    public function getCostsTimeSeries(?string $from, ?string $to): array
    {
        $query = Chat::query();
        $this->applyDateFilter($query, $from, $to);

        return $query
            ->selectRaw('DATE(created_at) as date, SUM(total_llm_cost) as llm, SUM(total_tts_cost) as tts, SUM(total_stt_cost) as stt, SUM(total_image_cost) as image')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->map(fn($row) => [
                'date' => $row->date,
                'llm' => round((float) $row->llm, 4),
                'tts' => round((float) $row->tts, 4),
                'stt' => round((float) $row->stt, 4),
                'image' => round((float) $row->image, 4),
            ])
            ->toArray();
    }

    public function getCostsPerModel(?string $from, ?string $to): array
    {
        $query = Message::query()->whereNotNull('provider')->whereNotNull('model');
        $this->applyDateFilterViaChat($query, $from, $to);

        return $query
            ->selectRaw('provider, model, COUNT(*) as messages_count, SUM(prompt_tokens + completion_tokens) as total_tokens, SUM(cost) as total_cost')
            ->groupBy('provider', 'model')
            ->orderByRaw('SUM(cost) DESC')
            ->get()
            ->map(fn($row) => [
                'provider' => $row->provider,
                'model' => $row->model,
                'messages_count' => (int) $row->messages_count,
                'total_tokens' => (int) $row->total_tokens,
                'total_cost' => round((float) $row->total_cost, 4),
            ])
            ->toArray();
    }

    public function getChatsPerDay(?string $from, ?string $to): array
    {
        $query = Chat::query();
        $this->applyDateFilter($query, $from, $to);

        return $query
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->map(fn($row) => [
                'date' => $row->date,
                'count' => (int) $row->count,
            ])
            ->toArray();
    }

    public function getProviderDistribution(?string $from, ?string $to): array
    {
        $query = Message::query()->where('role', 'bot')->whereNotNull('provider');
        $this->applyDateFilterViaChat($query, $from, $to);

        return $query
            ->selectRaw('provider, COUNT(*) as message_count')
            ->groupBy('provider')
            ->orderByRaw('COUNT(*) DESC')
            ->get()
            ->map(fn($row) => [
                'provider' => $row->provider,
                'message_count' => (int) $row->message_count,
            ])
            ->toArray();
    }

    public function getUsageStats(?string $from, ?string $to): array
    {
        $chatsQuery = Chat::query();
        $this->applyDateFilter($chatsQuery, $from, $to);

        $simpleCount = (clone $chatsQuery)->where('chat_type', 'simple')->count();
        $advancedCount = (clone $chatsQuery)->where('chat_type', 'advanced')->count();

        $chatIds = (clone $chatsQuery)->pluck('id');
        $totalMessages = Message::whereIn('chat_id', $chatIds)->count();
        $totalChats = $chatIds->count();
        $avgMessagesPerChat = $totalChats > 0 ? round($totalMessages / $totalChats, 1) : 0;

        return [
            'avg_messages_per_chat' => $avgMessagesPerChat,
            'simple_count' => $simpleCount,
            'advanced_count' => $advancedCount,
        ];
    }

    public function getScoreTrend(?string $from, ?string $to): array
    {
        $query = Evaluation::query()
            ->join('chats', 'evaluations.chat_id', '=', 'chats.id');
        $this->applyDateFilter($query, $from, $to, 'chats');

        return $query
            ->selectRaw('DATE(chats.created_at) as date, AVG(evaluations.overall_score) as avg_score, COUNT(*) as count')
            ->groupByRaw('DATE(chats.created_at)')
            ->orderByRaw('DATE(chats.created_at)')
            ->get()
            ->map(fn($row) => [
                'date' => $row->date,
                'avg_score' => round((float) $row->avg_score, 1),
                'count' => (int) $row->count,
            ])
            ->toArray();
    }

    public function getPassFailRate(?string $from, ?string $to): array
    {
        $query = Evaluation::query();
        $this->applyDateFilterViaChat($query, $from, $to);

        $total = (clone $query)->count();
        $passed = (clone $query)->whereRaw('overall_score >= 70')->count();

        return [
            'passed' => $passed,
            'failed' => $total - $passed,
        ];
    }

    public function getCriteriaPerformance(?string $from, ?string $to): array
    {
        $query = Evaluation::query();
        $this->applyDateFilterViaChat($query, $from, $to);

        $evaluations = $query->get();
        $criteriaMap = [];

        foreach (EvaluationCriteria::all() as $criterion) {
            $criteriaMap[$criterion['key']] = [
                'key' => $criterion['key'],
                'name' => $criterion['name'],
                'scores' => [],
            ];
        }

        foreach ($evaluations as $evaluation) {
            $results = $evaluation->criteria_results ?? [];
            foreach ($results as $result) {
                $key = $result['key'] ?? null;
                if ($key && isset($criteriaMap[$key])) {
                    $criteriaMap[$key]['scores'][] = (float) ($result['score'] ?? 0);
                }
            }
        }

        return array_values(array_map(function ($item) {
            $scores = $item['scores'];
            $count = count($scores);
            $avgScore = $count > 0 ? round(array_sum($scores) / $count, 1) : 0;
            $passCount = count(array_filter($scores, fn($s) => $s >= 70));
            $passRate = $count > 0 ? round(($passCount / $count) * 100, 1) : 0;

            return [
                'key' => $item['key'],
                'name' => $item['name'],
                'avg_score' => $avgScore,
                'pass_rate' => $passRate,
            ];
        }, $criteriaMap));
    }

    public function getEvaluationsByAgent(?string $from, ?string $to): array
    {
        $query = Evaluation::query()
            ->join('chats', 'evaluations.chat_id', '=', 'chats.id')
            ->join('agents', 'chats.agent_id', '=', 'agents.id');
        $this->applyDateFilter($query, $from, $to, 'chats');

        return $query
            ->selectRaw('agents.name as agent_name, AVG(evaluations.overall_score) as avg_score, COUNT(*) as eval_count, SUM(CASE WHEN evaluations.overall_score >= 70 THEN 1 ELSE 0 END) as passed_count')
            ->groupBy('agents.id', 'agents.name')
            ->orderBy('agents.name')
            ->get()
            ->map(fn($row) => [
                'agent_name' => $row->agent_name,
                'avg_score' => round((float) $row->avg_score, 1),
                'pass_rate' => $row->eval_count > 0 ? round(((float) $row->passed_count / (int) $row->eval_count) * 100, 1) : 0,
                'eval_count' => (int) $row->eval_count,
            ])
            ->toArray();
    }

    public function getAgentsSummary(?string $from, ?string $to): array
    {
        $agents = Agent::all();
        $result = [];

        foreach ($agents as $agent) {
            $chatsQuery = Chat::where('agent_id', $agent->id);
            $this->applyDateFilter($chatsQuery, $from, $to);

            $chatsCount = (clone $chatsQuery)->count();
            $totalCost = (clone $chatsQuery)->sum('total_cost');
            $avgCost = $chatsCount > 0 ? (float) $totalCost / $chatsCount : 0;

            $chatIds = (clone $chatsQuery)->pluck('id');
            $evalsQuery = Evaluation::whereIn('chat_id', $chatIds);
            $evalCount = (clone $evalsQuery)->count();
            $avgScore = (clone $evalsQuery)->avg('overall_score');
            $passedCount = (clone $evalsQuery)->whereRaw('overall_score >= 70')->count();
            $passRate = $evalCount > 0 ? round(($passedCount / $evalCount) * 100, 1) : 0;

            $result[] = [
                'name' => $agent->name,
                'chats_count' => $chatsCount,
                'avg_score' => round((float) ($avgScore ?? 0), 1),
                'pass_rate' => $passRate,
                'avg_cost' => round($avgCost, 4),
                'total_cost' => round((float) $totalCost, 4),
            ];
        }

        return $result;
    }

    private function applyDateFilter(Builder $query, ?string $from, ?string $to, string $table = null): void
    {
        $column = $table ? "{$table}.created_at" : 'created_at';

        if ($from) {
            $query->where($column, '>=', $from . ' 00:00:00');
        }
        if ($to) {
            $query->where($column, '<=', $to . ' 23:59:59');
        }
    }

    private function applyDateFilterViaChat(Builder $query, ?string $from, ?string $to): void
    {
        if ($from || $to) {
            $query->whereHas('chat', function (Builder $q) use ($from, $to) {
                $this->applyDateFilter($q, $from, $to);
            });
        }
    }
}
