<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function createLead(array $data, ?User $user = null): Lead
    {
        return DB::transaction(function () use ($data, $user) {
            $data['created_by'] = $user ? $user->id : ($data['created_by'] ?? null);
            if (empty($data['qualification_score'])) {
                $data['qualification_score'] = $this->calculateInitialScore($data);
            }

            $lead = Lead::create($data);

            if ($user) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'create',
                    'entity_type' => 'lead',
                    'entity_id' => $lead->id,
                    'new_values' => $lead->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $lead;
        });
    }

    public function updateLead(Lead $lead, array $data, ?User $user = null): Lead
    {
        return DB::transaction(function () use ($lead, $data, $user) {
            $oldValues = $lead->toArray();
            $lead->update($data);

            if ($user) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'update',
                    'entity_type' => 'lead',
                    'entity_id' => $lead->id,
                    'old_values' => $oldValues,
                    'new_values' => $lead->fresh()->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return $lead;
        });
    }

    public function convertLead(Lead $lead, array $options = [], ?User $user = null): array
    {
        return DB::transaction(function () use ($lead, $options, $user) {
            // 1. Create or Find Customer
            $customer = null;
            if (!empty($options['customer_id'])) {
                $customer = Customer::findOrFail($options['customer_id']);
            } elseif ($lead->customer_id) {
                $customer = $lead->customer;
            } else {
                $customer = Customer::create([
                    'company_name' => $lead->company_name ?: ($lead->contact_name . ' Company'),
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'assigned_to' => $lead->assigned_to,
                    'territory_id' => $lead->territory_id,
                    'status' => 'prospect',
                    'created_by' => $user ? $user->id : $lead->created_by,
                ]);
            }

            // 2. Create Contact
            $contact = null;
            if (!empty($lead->contact_name)) {
                $parts = explode(' ', trim($lead->contact_name), 2);
                $firstName = $parts[0] ?? 'Contact';
                $lastName = $parts[1] ?? 'Person';

                $contact = Contact::create([
                    'customer_id' => $customer->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'is_primary' => true,
                    'created_by' => $user ? $user->id : $lead->created_by,
                ]);
            }

            // 3. Create Opportunity
            $oppTitle = $options['opportunity_title'] ?? ($lead->title . ' - ' . $customer->company_name);
            $amount = $options['amount'] ?? $lead->estimated_value ?? 0;
            $opportunity = Opportunity::create([
                'title' => $oppTitle,
                'customer_id' => $customer->id,
                'contact_id' => $contact ? $contact->id : null,
                'lead_id' => $lead->id,
                'stage' => 'qualification',
                'probability' => 25,
                'amount' => $amount,
                'expected_revenue' => $amount * 0.25,
                'currency' => $lead->currency ?: 'USD',
                'close_date' => $lead->expected_close_date ?: now()->addDays(30)->toDateString(),
                'assigned_to' => $lead->assigned_to,
                'territory_id' => $lead->territory_id,
                'created_by' => $user ? $user->id : $lead->created_by,
            ]);

            // 4. Update Lead status
            $lead->update([
                'status' => 'converted',
                'customer_id' => $customer->id,
                'converted_to_opportunity_id' => $opportunity->id,
                'converted_at' => now(),
            ]);

            return [
                'customer' => $customer,
                'contact' => $contact,
                'opportunity' => $opportunity,
                'lead' => $lead->fresh(),
            ];
        });
    }

    private function calculateInitialScore(array $data): int
    {
        $score = 20;
        if (!empty($data['email'])) $score += 15;
        if (!empty($data['phone'])) $score += 15;
        if (!empty($data['company_name'])) $score += 15;
        if (!empty($data['estimated_value']) && $data['estimated_value'] > 5000) $score += 20;
        if (in_array($data['source'] ?? '', ['referral', 'website'])) $score += 15;
        return min(100, $score);
    }
}
