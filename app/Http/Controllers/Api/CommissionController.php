<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Commission::with(['user', 'approver', 'adjustments']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $commissions = $query->orderBy('period', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($commissions);
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'period_type' => 'nullable|in:monthly,quarterly,yearly',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $periodType = $validated['period_type'] ?? 'monthly';
        $commissionRate = isset($validated['commission_rate']) ? (float)$validated['commission_rate'] : 5.0;

        $commission = $this->commissionService->calculateUserCommission(
            (int)$validated['user_id'],
            $validated['period'],
            $periodType,
            $commissionRate
        );

        return response()->json([
            'message' => 'Commission calculated successfully.',
            'commission' => $commission,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $commission = Commission::with(['user.territory', 'approver', 'adjustments'])->findOrFail($id);
        return response()->json($commission);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $commission = Commission::findOrFail($id);
        $commission = $this->commissionService->approveCommission($commission, $request->user());

        return response()->json([
            'message' => 'Commission approved successfully.',
            'commission' => $commission,
        ]);
    }

    public function pay(Request $request, int $id): JsonResponse
    {
        $commission = Commission::findOrFail($id);
        $commission = $this->commissionService->markPaid($commission);

        return response()->json([
            'message' => 'Commission marked as paid.',
            'commission' => $commission,
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $period = $request->get('period', date('Y-m'));

        $totalCommissions = (float) Commission::where('period', $period)->sum('commission_amount');
        $totalBonuses = (float) Commission::where('period', $period)->sum('bonus_amount');
        $pendingApproval = Commission::where('period', $period)->where('status', 'pending')->count();
        $totalPaid = (float) Commission::where('period', $period)->where('status', 'paid')->sum('commission_amount');

        return response()->json([
            'period' => $period,
            'total_commissions' => $totalCommissions,
            'total_bonuses' => $totalBonuses,
            'total_payout' => $totalCommissions + $totalBonuses,
            'pending_approval_count' => $pendingApproval,
            'total_paid' => $totalPaid,
        ]);
    }

    public function addAdjustment(Request $request, int $id): JsonResponse
    {
        $commission = Commission::findOrFail($id);
        $validated = $request->validate([
            'type' => 'required|in:bonus,penalty,adjustment',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        $adj = $this->commissionService->addAdjustment($commission, $validated['type'], (float)$validated['amount'], $validated['reason'] ?? null);

        return response()->json([
            'message' => 'Adjustment added successfully.',
            'adjustment' => $adj,
            'commission' => $commission->fresh(['adjustments']),
        ], 201);
    }
}
