<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Team;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $repRole = Role::where('name', 'sales_rep')->first();

        $territories = Territory::all()->keyBy('name');
        $teams = Team::all()->keyBy('name');

        // 1. System Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@saleserp.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'territory_id' => $territories['Global Sales Territory']->id ?? null,
                'team_id' => null,
                'is_active' => true,
                'phone' => '+1 (555) 010-0001',
                'last_login_at' => now(),
            ]
        );
        if ($adminRole && !$admin->roles()->where('name', 'admin')->exists()) {
            $admin->roles()->attach($adminRole->id);
        }

        // 2. Sales Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@saleserp.com'],
            [
                'name' => 'Sales Operations Manager',
                'password' => Hash::make('password123'),
                'role' => 'manager',
                'territory_id' => $territories['US West & Silicon Valley']->id ?? ($territories['Global Sales Territory']->id ?? null),
                'team_id' => $teams['Enterprise Strategic Accounts']->id ?? null,
                'is_active' => true,
                'phone' => '+1 (555) 010-0002',
                'last_login_at' => now(),
            ]
        );
        if ($managerRole && !$manager->roles()->where('name', 'manager')->exists()) {
            $manager->roles()->attach($managerRole->id);
        }

        // 3. Sales Representative
        $rep = User::firstOrCreate(
            ['email' => 'rep@saleserp.com'],
            [
                'name' => 'Senior Account Executive',
                'password' => Hash::make('password123'),
                'role' => 'sales_rep',
                'territory_id' => $territories['US West & Silicon Valley']->id ?? ($territories['Global Sales Territory']->id ?? null),
                'team_id' => $teams['Enterprise Strategic Accounts']->id ?? null,
                'is_active' => true,
                'phone' => '+1 (555) 010-0010',
                'last_login_at' => now(),
            ]
        );
        if ($repRole && !$rep->roles()->where('name', 'sales_rep')->exists()) {
            $rep->roles()->attach($repRole->id);
        }
    }
}
