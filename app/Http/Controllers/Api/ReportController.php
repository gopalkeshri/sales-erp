<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function salesSummary(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $summary = $this->reportService->getSalesSummary($startDate, $endDate);
        return response()->json($summary);
    }

    public function pipeline(Request $request): JsonResponse
    {
        $metrics = $this->reportService->getDashboardMetrics($request->user());
        return response()->json($metrics);
    }

    public function forecast(Request $request): JsonResponse
    {
        $trends = $this->reportService->getRevenueTrends(12);
        return response()->json($trends);
    }

    public function topPerformers(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $top = $this->reportService->getTopPerformers((int)$limit);
        return response()->json($top);
    }

    public function territoryPerformance(): JsonResponse
    {
        $territories = $this->reportService->getTerritoryPerformance();
        return response()->json($territories);
    }

    public function productPerformance(): JsonResponse
    {
        $products = $this->reportService->getProductPerformance();
        return response()->json($products);
    }

    public function revenueTrends(Request $request): JsonResponse
    {
        $months = $request->get('months', 6);
        $trends = $this->reportService->getRevenueTrends((int)$months);
        return response()->json($trends);
    }

    public function taxSummary(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $taxSummary = $this->reportService->getTaxSummary($startDate, $endDate);
        return response()->json($taxSummary);
    }
}
