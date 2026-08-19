<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Commission;
use App\Models\CommissionAdjustment;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommissionAndActivitySeeder extends Seeder
{
    public function run(): void
    {
        $reps = User::where('role', 'sales_rep')->get();
        $managers = User::where('role', 'manager')->get();
        $opportunities = Opportunity::all();
        $leads = Lead::all();
        $customers = Customer::all();

        // 1. Commissions for previous and current month
        $periods = [date('Y-m', strtotime('-1 month')), date('Y-m')];

        foreach ($reps as $rep) {
            foreach ($periods as $pIndex => $period) {
                $sales = ($pIndex === 0) ? rand(35000, 110000) : rand(20000, 75000);
                $rate = 5.00;
                $commAmount = $sales * ($rate / 100);
                $bonus = ($sales >= 100000) ? 3000.00 : (($sales >= 50000) ? 1000.00 : 0.00);

                $commission = Commission::create([
                    'user_id' => $rep->id,
                    'period' => $period,
                    'period_type' => 'monthly',
                    'total_sales' => $sales,
                    'commission_rate' => $rate,
                    'commission_amount' => $commAmount,
                    'bonus_amount' => $bonus,
                    'status' => ($pIndex === 0) ? 'paid' : 'approved',
                    'approved_by' => $managers->first()->id ?? null,
                    'approved_at' => now()->subDays(5),
                    'paid_at' => ($pIndex === 0) ? now()->subDays(2) : null,
                ]);

                if ($bonus > 0) {
                    CommissionAdjustment::create([
                        'commission_id' => $commission->id,
                        'type' => 'bonus',
                        'amount' => $bonus,
                        'reason' => 'Q3 Enterprise sales quota achievement bonus',
                        'created_at' => now(),
                    ]);
                }
            }
        }

        // 2. Activities Timeline
        $activityTypes = ['call', 'email', 'meeting', 'task', 'note'];
        $activitySubjects = [
            'Introductory Discovery Call on Architecture',
            'Sent Proposal and SLA Terms document',
            'On-site Technical Review with VP Engineering',
            'Follow-up regarding Security Audit compliance',
            'Contract Review and Pricing Negotiation session',
        ];

        foreach ($opportunities as $i => $opp) {
            $rep = $opp->assignedUser ?? $reps->first();
            Activity::create([
                'type' => $activityTypes[$i % count($activityTypes)],
                'subject' => $activitySubjects[$i % count($activitySubjects)],
                'description' => 'Detailed discussion on deployment timeline, SLA requirements, and integration milestones.',
                'entity_type' => 'opportunity',
                'entity_id' => $opp->id,
                'performed_by' => $rep ? $rep->id : null,
                'assigned_to' => $rep ? $rep->id : null,
                'scheduled_at' => now()->subDays(rand(1, 10)),
                'completed_at' => now()->subDays(rand(0, 5)),
                'duration' => rand(30, 60),
                'outcome' => 'Positive feedback, proceeding to next phase.',
            ]);
        }

        foreach ($leads as $i => $lead) {
            $rep = $lead->assignedUser ?? $reps->first();
            Activity::create([
                'type' => 'call',
                'subject' => 'Initial Lead Qualification Phone Call',
                'description' => 'Confirmed current infrastructure scale and budget allocation for 2026.',
                'entity_type' => 'lead',
                'entity_id' => $lead->id,
                'performed_by' => $rep ? $rep->id : null,
                'assigned_to' => $rep ? $rep->id : null,
                'scheduled_at' => now()->subDays(2),
                'completed_at' => now()->subDays(1),
                'duration' => 25,
                'outcome' => 'Lead qualified, scheduled deep dive.',
            ]);
        }
    }
}
