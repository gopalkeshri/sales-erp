<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Opportunity;
use App\Services\OpportunityService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ReportService $reportService;
    protected OpportunityService $opportunityService;

    public function __construct(ReportService $reportService, OpportunityService $opportunityService)
    {
        $this->reportService = $reportService;
        $this->opportunityService = $opportunityService;
    }

    public function stats(Request $request): JsonResponse
    {
        $metrics = $this->reportService->getDashboardMetrics($request->user());
        return response()->json($metrics);
    }

    public function revenueChart(Request $request): JsonResponse
    {
        $months = $request->get('months', 6);
        $trends = $this->reportService->getRevenueTrends((int)$months);
        return response()->json($trends);
    }

    public function pipelineChart(Request $request): JsonResponse
    {
        $pipeline = $this->opportunityService->getPipelineSummary();
        $formatted = [];
        foreach ($pipeline as $stage => $data) {
            $formatted[] = [
                'stage' => ucfirst(str_replace('_', ' ', $stage)),
                'stage_key' => $stage,
                'count' => $data['count'],
                'amount' => $data['total_amount'],
            ];
        }
        return response()->json($formatted);
    }

    public function activities(Request $request): JsonResponse
    {
        $activities = Activity::with(['performer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($activities);
    }

    public function tasks(Request $request): JsonResponse
    {
        $tasks = Activity::where('type', 'task')
            ->whereNull('completed_at')
            ->with(['performer', 'assignedUser'])
            ->orderBy('scheduled_at', 'asc')
            ->limit(10)
            ->get();

        return response()->json($tasks);
    }
}
