<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request): JsonResponse
    {
        $from = $request->query('from');
        $to = $request->query('to');

        return response()->json([
            'status' => true,
            'data' => [
                'kpis' => $this->dashboardService->getKpis($from, $to),
                'costs_by_service' => $this->dashboardService->getCostsByServiceType($from, $to),
                'costs_by_provider' => $this->dashboardService->getCostsByProvider($from, $to),
                'costs_time_series' => $this->dashboardService->getCostsTimeSeries($from, $to),
                'costs_per_model' => $this->dashboardService->getCostsPerModel($from, $to),
                'chats_per_day' => $this->dashboardService->getChatsPerDay($from, $to),
                'provider_distribution' => $this->dashboardService->getProviderDistribution($from, $to),
                'usage_stats' => $this->dashboardService->getUsageStats($from, $to),
                'score_trend' => $this->dashboardService->getScoreTrend($from, $to),
                'pass_fail_rate' => $this->dashboardService->getPassFailRate($from, $to),
                'criteria_performance' => $this->dashboardService->getCriteriaPerformance($from, $to),
                'evaluations_by_agent' => $this->dashboardService->getEvaluationsByAgent($from, $to),
                'agents_summary' => $this->dashboardService->getAgentsSummary($from, $to),
            ],
            'message' => 'OK',
            'errors' => [],
        ]);
    }
}
