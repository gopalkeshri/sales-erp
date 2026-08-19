<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Contact;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerAndContactSeeder extends Seeder
{
    public function run(): void
    {
        $reps = User::where('role', 'sales_rep')->get();
        $territories = Territory::whereNotNull('parent_territory_id')->get();

        $customersData = [
            [
                'company_name' => 'Acme Corporation',
                'trade_name' => 'Acme Cloud & Logistics',
                'gst_number' => '27AACCA1234F1Z5',
                'pan_number' => 'AACCA1234F',
                'industry' => 'Technology & Cloud',
                'website' => 'https://acme.example.com',
                'phone' => '+1 (555) 234-5678',
                'email' => 'procurement@acme.example.com',
                'address_street' => '500 Market St, Suite 400',
                'address_city' => 'San Francisco',
                'address_state' => 'CA',
                'address_country' => 'United States',
                'address_postal_code' => '94105',
                'billing_street' => '500 Market St, Suite 400',
                'billing_city' => 'San Francisco',
                'billing_state' => 'CA',
                'billing_country' => 'United States',
                'billing_postal_code' => '94105',
                'shipping_street' => '1200 Logistics Way',
                'shipping_city' => 'Oakland',
                'shipping_state' => 'CA',
                'shipping_country' => 'United States',
                'shipping_postal_code' => '94607',
                'type' => 'enterprise',
                'status' => 'active',
                'credit_limit' => 250000.00,
                'payment_terms' => 'net_30',
                'contacts' => [
                    [
                        'first_name' => 'Michael',
                        'last_name' => 'Chang',
                        'email' => 'michael.chang@acme.example.com',
                        'phone' => '+1 (555) 234-5679',
                        'mobile' => '+1 (555) 890-1234',
                        'designation' => 'Chief Technology Officer',
                        'department' => 'Engineering & IT',
                        'is_decision_maker' => true,
                        'is_primary' => true,
                    ],
                    [
                        'first_name' => 'Linda',
                        'last_name' => 'Holloway',
                        'email' => 'linda.h@acme.example.com',
                        'phone' => '+1 (555) 234-5680',
                        'mobile' => '+1 (555) 890-1235',
                        'designation' => 'VP of Global Procurement',
                        'department' => 'Finance & Sourcing',
                        'is_decision_maker' => true,
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'company_name' => 'Nexus Global Technologies',
                'trade_name' => 'Nexus Systems UK',
                'gst_number' => 'GB999888777',
                'pan_number' => 'NEXUS99988',
                'industry' => 'FinTech & Banking',
                'website' => 'https://nexusglobal.example.com',
                'phone' => '+44 20 7123 4567',
                'email' => 'vendor-management@nexusglobal.example.com',
                'address_street' => '25 Bank Street, Canary Wharf',
                'address_city' => 'London',
                'address_country' => 'United Kingdom',
                'address_postal_code' => 'E14 5JP',
                'type' => 'enterprise',
                'status' => 'active',
                'credit_limit' => 500000.00,
                'payment_terms' => 'net_60',
                'contacts' => [
                    [
                        'first_name' => 'Jonathan',
                        'last_name' => 'Sterling',
                        'email' => 'j.sterling@nexusglobal.example.com',
                        'phone' => '+44 20 7123 4568',
                        'designation' => 'Head of Infrastructure',
                        'department' => 'IT Infrastructure',
                        'is_decision_maker' => true,
                        'is_primary' => true,
                    ],
                ],
            ],
            [
                'company_name' => 'Horizon Healthcare Solutions',
                'trade_name' => 'Horizon Health',
                'gst_number' => '07AABCH5678G1Z2',
                'industry' => 'Healthcare & BioTech',
                'website' => 'https://horizonhealth.example.com',
                'phone' => '+1 (555) 456-7890',
                'email' => 'it-director@horizonhealth.example.com',
                'address_street' => '789 Medical Center Drive',
                'address_city' => 'New York',
                'address_state' => 'NY',
                'address_country' => 'United States',
                'address_postal_code' => '10001',
                'type' => 'mid_market',
                'status' => 'active',
                'credit_limit' => 150000.00,
                'payment_terms' => 'net_30',
                'contacts' => [
                    [
                        'first_name' => 'Dr. Rebecca',
                        'last_name' => 'Alvarez',
                        'email' => 'ralvarez@horizonhealth.example.com',
                        'phone' => '+1 (555) 456-7891',
                        'designation' => 'Chief Information Officer',
                        'department' => 'Executive',
                        'is_decision_maker' => true,
                        'is_primary' => true,
                    ],
                ],
            ],
            [
                'company_name' => 'Vertex Quantum Analytics',
                'trade_name' => 'Vertex AI',
                'industry' => 'Artificial Intelligence',
                'website' => 'https://vertexai.example.com',
                'phone' => '+65 6123 9876',
                'email' => 'ops@vertexai.example.com',
                'address_street' => '10 Marina Boulevard, Tower 2',
                'address_city' => 'Singapore',
                'address_country' => 'Singapore',
                'address_postal_code' => '018983',
                'type' => 'small_business',
                'status' => 'prospect',
                'credit_limit' => 75000.00,
                'payment_terms' => 'due_on_receipt',
                'contacts' => [
                    [
                        'first_name' => 'Wei',
                        'last_name' => 'Tan',
                        'email' => 'wei.tan@vertexai.example.com',
                        'phone' => '+65 6123 9877',
                        'designation' => 'Managing Director & Co-Founder',
                        'department' => 'Management',
                        'is_decision_maker' => true,
                        'is_primary' => true,
                    ],
                ],
            ],
        ];

        foreach ($customersData as $i => $cData) {
            $contacts = $cData['contacts'] ?? [];
            unset($cData['contacts']);

            $rep = $reps[$i % $reps->count()] ?? null;
            $cData['assigned_to'] = $rep ? $rep->id : null;
            $cData['territory_id'] = $rep ? $rep->territory_id : ($territories->first()->id ?? null);
            $cData['created_by'] = $rep ? $rep->id : null;

            $customer = Customer::create($cData);

            foreach ($contacts as $contactData) {
                $contactData['customer_id'] = $customer->id;
                $contactData['created_by'] = $rep ? $rep->id : null;
                Contact::create($contactData);
            }
        }
    }
}
