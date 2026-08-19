<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN-01'],
            [
                'name' => 'Primary Central Distribution Hub',
                'street' => '100 Enterprise Way',
                'city' => 'San Francisco',
                'state' => 'CA',
                'country' => 'United States',
                'postal_code' => '94105',
                'is_active' => true,
            ]
        );

        Warehouse::firstOrCreate(
            ['code' => 'WH-SEC-02'],
            [
                'name' => 'Secondary Logistics Depot',
                'street' => '450 Harbor Boulevard',
                'city' => 'Newark',
                'state' => 'NJ',
                'country' => 'United States',
                'postal_code' => '07102',
                'is_active' => true,
            ]
        );
    }
}
