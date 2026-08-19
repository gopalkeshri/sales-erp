<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            TerritoryAndTeamSeeder::class,
            UserSeeder::class,
            ProductAndWarehouseSeeder::class,
            CustomerAndContactSeeder::class,
            LeadAndOpportunitySeeder::class,
            QuoteAndOrderSeeder::class,
            InvoiceAndPaymentSeeder::class,
            CommissionAndActivitySeeder::class,
            SettingSeeder::class,
        ]);
    }
}
