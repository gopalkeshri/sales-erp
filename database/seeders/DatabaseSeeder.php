<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with essential production structure (0 dummy transactions).
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            SettingSeeder::class,
            TerritoryAndTeamSeeder::class,
            WarehouseSeeder::class,
            UserSeeder::class,
        ]);
    }
}
