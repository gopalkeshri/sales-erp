<?php

namespace App\Services;

use App\Models\Opportunity;
use App\Models\OpportunityProduct;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class OpportunityService
{
    private array $stageProbabilities = [
        'prospecting' => 10,
        'qualification' => 25,
        'proposal' => 50,
        'negotiation' => 75,
        'closed_won' => 100,
        'closed_lost' => 0,
    ];

    public function createOpportunity(array $data, array $products = [], ?User $user = null): Opportunity
    {
        return DB::transaction(function () use ($data, $products, $user) {
            $stage = $data['stage'] ?? 'prospecting';
            $probability = $data['probability'] ?? ($this->stageProbabilities[$stage] ?? 10);
            $amount = $data['amount'] ?? 0;

            $data['stage'] = $stage;
            $data['probability'] = $probability;
            $data['amount'] = $amount;
            $data['expected_revenue'] = $amount * ($probability / 100);
            $data['created_by'] = $user ? $user->id : ($data['created_by'] ?? null);

            $opportunity = Opportunity::create($data);

            if (!empty($products)) {
                $totalFromProducts = 0;
                foreach ($products as $p) {
                    $qty = $p['quantity'] ?? 1;
                    $unitPrice = $p['unit_price'] ?? 0;
                    $discount = $p['discount'] ?? 0;
                    $total = ($qty * $unitPrice) - $discount;

                    OpportunityProduct::create([
                        'opportunity_id' => $opportunity->id,
                        'product_id' => $p['product_id'],
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'discount' => $discount,
                        'total' => $total,
                    ]);

                    $totalFromProducts += $total;
                }

                if ($totalFromProducts > 0) {
                    $opportunity->update([
                        'amount' => $totalFromProducts,
                        'expected_revenue' => $totalFromProducts * ($probability / 100),
                    ]);
                }
            }

            if ($user) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'create',
                    'entity_type' => 'opportunity',
                    'entity_id' => $opportunity->id,
                    'new_values' => $opportunity->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $opportunity->load('opportunityProducts.product', 'customer', 'contact', 'assignedUser');
        });
    }

    public function updateStage(Opportunity $opportunity, string $newStage, ?string $lostReason = null, ?User $user = null): Opportunity
    {
        return DB::transaction(function () use ($opportunity, $newStage, $lostReason, $user) {
            $oldValues = $opportunity->toArray();
            $probability = $this->stageProbabilities[$newStage] ?? $opportunity->probability;

            $updateData = [
                'stage' => $newStage,
                'probability' => $probability,
                'expected_revenue' => (float)$opportunity->amount * ($probability / 100),
            ];

            if ($newStage === 'closed_won') {
                $updateData['actual_close_date'] = now()->toDateString();
            } elseif ($newStage === 'closed_lost') {
                $updateData['actual_close_date'] = now()->toDateString();
                $updateData['lost_reason'] = $lostReason;
            }

            $opportunity->update($updateData);

            if ($user) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'update',
                    'entity_type' => 'opportunity',
                    'entity_id' => $opportunity->id,
                    'old_values' => $oldValues,
                    'new_values' => $opportunity->fresh()->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $opportunity->fresh(['customer', 'contact', 'assignedUser']);
        });
    }

    public function getPipelineSummary(?int $userId = null, ?int $territoryId = null): array
    {
        $query = Opportunity::query();

        if ($userId) {
            $query->where('assigned_to', $userId);
        }
        if ($territoryId) {
            $query->where('territory_id', $territoryId);
        }

        $stages = ['prospecting', 'qualification', 'proposal', 'negotiation', 'closed_won', 'closed_lost'];
        $summary = [];

        foreach ($stages as $stage) {
            $opps = (clone $query)->where('stage', $stage)->with(['customer', 'assignedUser', 'contact'])->get();
            $summary[$stage] = [
                'count' => $opps->count(),
                'total_amount' => (float) $opps->sum('amount'),
                'expected_revenue' => (float) $opps->sum('expected_revenue'),
                'deals' => $opps,
            ];
        }

        return $summary;
    }
}
