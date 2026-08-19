<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed the application's database with sample demonstration records.
     */
    public function run(): void
    {
        $this->call([
            ProductAndWarehouseSeeder::class,
            CustomerAndContactSeeder::class,
            LeadAndOpportunitySeeder::class,
            QuoteAndOrderSeeder::class,
            InvoiceAndPaymentSeeder::class,
            CommissionAndActivitySeeder::class,
        ]);
    }
}
