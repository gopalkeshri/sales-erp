<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\OpportunityProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadAndOpportunitySeeder extends Seeder
{
    public function run(): void
    {
        $reps = User::where('role', 'sales_rep')->get();
        $customers = Customer::with('contacts')->get();
        $products = Product::all();

        // 1. Leads
        $leadsData = [
            [
                'title' => 'Inbound Request - Cloud Security & ERP Integration',
                'source' => 'website',
                'source_detail' => 'Pricing Page Form Submission',
                'company_name' => 'Starlight Semiconductor Corp',
                'contact_name' => 'David Sterling',
                'email' => 'd.sterling@starlightsemi.example.com',
                'phone' => '+1 (555) 301-4455',
                'status' => 'new',
                'qualification_score' => 85,
                'estimated_value' => 48000.00,
                'expected_close_date' => now()->addDays(45)->toDateString(),
                'notes' => 'Looking to replace legacy SAP instance with modular Sales ERP.',
            ],
            [
                'title' => 'Referral - Datacenter GPU Server Expansion',
                'source' => 'referral',
                'source_detail' => 'Referred by Acme Corp CTO',
                'company_name' => 'Omni AI Labs',
                'contact_name' => 'Elena Patel',
                'email' => 'elena.patel@omniai.example.com',
                'phone' => '+1 (555) 301-7788',
                'status' => 'qualified',
                'qualification_score' => 95,
                'estimated_value' => 114000.00,
                'expected_close_date' => now()->addDays(20)->toDateString(),
                'notes' => 'Urgent requirement for 4x Rackmount AI Servers for LLM inference cluster.',
            ],
            [
                'title' => 'Trade Show Contact - Dev Workstations Fleet Upgrade',
                'source' => 'trade_show',
                'source_detail' => 'TechCrunch Disrupt 2026',
                'company_name' => 'HyperScale Games Studio',
                'contact_name' => 'Chris Robertson',
                'email' => 'chris@hyperscalegames.example.com',
                'phone' => '+1 (555) 301-9922',
                'status' => 'contacted',
                'qualification_score' => 60,
                'estimated_value' => 32000.00,
                'expected_close_date' => now()->addDays(60)->toDateString(),
                'notes' => 'Requested demo of developer workstation performance vs MacBook Pro M3.',
            ],
            [
                'title' => 'Outbound SDR Lead - European Regional Expansion',
                'source' => 'cold_call',
                'source_detail' => 'Outreach Campaign Q3',
                'company_name' => 'Bavaria Logistics AG',
                'contact_name' => 'Hans Gruber',
                'email' => 'h.gruber@bavialog.example.de',
                'phone' => '+49 89 123456',
                'status' => 'unqualified',
                'qualification_score' => 30,
                'estimated_value' => 15000.00,
                'notes' => 'Budget cycle postponed until next fiscal year.',
            ],
        ];

        foreach ($leadsData as $i => $leadData) {
            $rep = $reps[$i % $reps->count()] ?? null;
            $leadData['assigned_to'] = $rep ? $rep->id : null;
            $leadData['territory_id'] = $rep ? $rep->territory_id : null;
            $leadData['created_by'] = $rep ? $rep->id : null;
            Lead::create($leadData);
        }

        // 2. Opportunities across all stages
        $opportunitiesData = [
            [
                'title' => 'Acme Corp - Enterprise Cloud & Security Expansion',
                'customer_index' => 0,
                'stage' => 'negotiation',
                'probability' => 75,
                'amount' => 84000.00,
                'close_date' => now()->addDays(15)->toDateString(),
                'products' => [
                    ['sku' => 'SW-ERP-ENT', 'quantity' => 2, 'unit_price' => 12000.00, 'discount' => 2000.00],
                    ['sku' => 'SW-SEC-SHIELD', 'quantity' => 4, 'unit_price' => 4500.00, 'discount' => 0.00],
                    ['sku' => 'HW-SRV-R900', 'quantity' => 2, 'unit_price' => 28500.00, 'discount' => 3000.00],
                ],
            ],
            [
                'title' => 'Nexus Global - UK Datacenter Upgrade',
                'customer_index' => 1,
                'stage' => 'proposal',
                'probability' => 50,
                'amount' => 122900.00,
                'close_date' => now()->addDays(30)->toDateString(),
                'products' => [
                    ['sku' => 'HW-SRV-R900', 'quantity' => 3, 'unit_price' => 28500.00, 'discount' => 5000.00],
                    ['sku' => 'HW-NET-SW48G', 'quantity' => 4, 'unit_price' => 8900.00, 'discount' => 2000.00],
                    ['sku' => 'SV-IMPL-PACK', 'quantity' => 2, 'unit_price' => 7500.00, 'discount' => 0.00],
                ],
            ],
            [
                'title' => 'Horizon Health - Secure Telehealth Core Infrastructure',
                'customer_index' => 2,
                'stage' => 'closed_won',
                'probability' => 100,
                'amount' => 58000.00,
                'close_date' => now()->subDays(10)->toDateString(),
                'actual_close_date' => now()->subDays(10)->toDateString(),
                'products' => [
                    ['sku' => 'SW-ERP-ENT', 'quantity' => 1, 'unit_price' => 12000.00, 'discount' => 0.00],
                    ['sku' => 'SW-SEC-SHIELD', 'quantity' => 2, 'unit_price' => 4500.00, 'discount' => 0.00],
                    ['sku' => 'HW-WRK-PRO16', 'quantity' => 10, 'unit_price' => 3200.00, 'discount' => 2000.00],
                    ['sku' => 'SV-IMPL-PACK', 'quantity' => 1, 'unit_price' => 7500.00, 'discount' => 500.00],
                ],
            ],
            [
                'title' => 'Vertex AI - APAC Compute Cluster Deployment',
                'customer_index' => 3,
                'stage' => 'prospecting',
                'probability' => 10,
                'amount' => 95000.00,
                'close_date' => now()->addDays(60)->toDateString(),
                'products' => [
                    ['sku' => 'HW-SRV-R900', 'quantity' => 3, 'unit_price' => 28500.00, 'discount' => 0.00],
                    ['sku' => 'HW-NET-SW48G', 'quantity' => 1, 'unit_price' => 8900.00, 'discount' => 0.00],
                ],
            ],
        ];

        foreach ($opportunitiesData as $i => $oppData) {
            $customer = $customers[$oppData['customer_index']] ?? $customers->first();
            $contact = $customer->primaryContact ?? $customer->contacts->first();
            $rep = $customer->assignedUser ?? $reps->first();

            $productsList = $oppData['products'];
            unset($oppData['products'], $oppData['customer_index']);

            $oppData['customer_id'] = $customer->id;
            $oppData['contact_id'] = $contact ? $contact->id : null;
            $oppData['assigned_to'] = $rep ? $rep->id : null;
            $oppData['team_id'] = $rep ? $rep->team_id : null;
            $oppData['territory_id'] = $customer->territory_id;
            $oppData['expected_revenue'] = $oppData['amount'] * ($oppData['probability'] / 100);
            $oppData['currency'] = 'USD';
            $oppData['created_by'] = $rep ? $rep->id : null;

            $opportunity = Opportunity::create($oppData);

            foreach ($productsList as $pItem) {
                $product = Product::where('sku', $pItem['sku'])->first();
                if ($product) {
                    $total = ($pItem['quantity'] * $pItem['unit_price']) - $pItem['discount'];
                    OpportunityProduct::create([
                        'opportunity_id' => $opportunity->id,
                        'product_id' => $product->id,
                        'quantity' => $pItem['quantity'],
                        'unit_price' => $pItem['unit_price'],
                        'discount' => $pItem['discount'],
                        'total' => $total,
                    ]);
                }
            }
        }
    }
}
