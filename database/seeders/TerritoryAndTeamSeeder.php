<?php

namespace Database\Seeders;

use App\Models\Territory;
use App\Models\Team;
use Illuminate\Database\Seeder;

class TerritoryAndTeamSeeder extends Seeder
{
    public function run(): void
    {
        // Global Parent
        $global = Territory::create([
            'name' => 'Global Sales Territory',
            'description' => 'Worldwide corporate coverage',
            'region' => 'Global',
            'country' => 'Global',
            'is_active' => true,
        ]);

        // Regions
        $na = Territory::create([
            'name' => 'North America Region',
            'parent_territory_id' => $global->id,
            'region' => 'Americas',
            'country' => 'United States',
            'is_active' => true,
        ]);

        $naWest = Territory::create([
            'name' => 'US West & Silicon Valley',
            'parent_territory_id' => $na->id,
            'region' => 'Americas',
            'country' => 'United States',
            'state' => 'California',
            'city' => 'San Francisco',
            'postal_codes' => ['94101', '94105', '94016', '94025'],
            'is_active' => true,
        ]);

        $naEast = Territory::create([
            'name' => 'US East & Financial District',
            'parent_territory_id' => $na->id,
            'region' => 'Americas',
            'country' => 'United States',
            'state' => 'New York',
            'city' => 'New York',
            'postal_codes' => ['10001', '10004', '10005'],
            'is_active' => true,
        ]);

        $emea = Territory::create([
            'name' => 'EMEA Hub (Europe & Middle East)',
            'parent_territory_id' => $global->id,
            'region' => 'Europe',
            'country' => 'United Kingdom',
            'city' => 'London',
            'is_active' => true,
        ]);

        $apac = Territory::create([
            'name' => 'APAC & Emerging Markets',
            'parent_territory_id' => $global->id,
            'region' => 'Asia Pacific',
            'country' => 'Singapore',
            'city' => 'Singapore',
            'is_active' => true,
        ]);

        // Teams
        Team::create([
            'name' => 'Enterprise Strategic Accounts',
            'territory_id' => $na->id,
            'description' => 'Dedicated to Fortune 500 enterprise software & infrastructure deals.',
            'is_active' => true,
        ]);

        Team::create([
            'name' => 'Mid-Market Velocity Team',
            'territory_id' => $naWest->id,
            'description' => 'Fast-cycle growth deals for tech and mid-market companies.',
            'is_active' => true,
        ]);

        Team::create([
            'name' => 'EMEA Expansion Team',
            'territory_id' => $emea->id,
            'description' => 'European cross-border enterprise technology licensing.',
            'is_active' => true,
        ]);
    }
}
