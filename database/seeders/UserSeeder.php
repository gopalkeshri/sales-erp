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

        // 1. Admin User
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@saleserp.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'territory_id' => $territories['Global Sales Territory']->id ?? null,
            'team_id' => null,
            'is_active' => true,
            'phone' => '+1 (555) 010-0001',
            'last_login_at' => now(),
        ]);
        if ($adminRole) $admin->roles()->attach($adminRole->id);

        // 2. Sales Managers
        $sarah = User::create([
            'name' => 'Sarah Jenkins',
            'email' => 'sarah.manager@saleserp.com',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'territory_id' => $territories['US West & Silicon Valley']->id ?? null,
            'team_id' => $teams['Enterprise Strategic Accounts']->id ?? null,
            'is_active' => true,
            'phone' => '+1 (555) 010-0002',
            'last_login_at' => now()->subHours(2),
        ]);
        if ($managerRole) $sarah->roles()->attach($managerRole->id);

        $marcus = User::create([
            'name' => 'Marcus Vance',
            'email' => 'marcus.manager@saleserp.com',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'territory_id' => $territories['EMEA Hub (Europe & Middle East)']->id ?? null,
            'team_id' => $teams['EMEA Expansion Team']->id ?? null,
            'is_active' => true,
            'phone' => '+44 20 7946 0912',
            'last_login_at' => now()->subDay(),
        ]);
        if ($managerRole) $marcus->roles()->attach($managerRole->id);

        // Assign managers to territories and teams
        if (isset($territories['US West & Silicon Valley'])) {
            $territories['US West & Silicon Valley']->update(['manager_id' => $sarah->id]);
        }
        if (isset($territories['EMEA Hub (Europe & Middle East)'])) {
            $territories['EMEA Hub (Europe & Middle East)']->update(['manager_id' => $marcus->id]);
        }
        if (isset($teams['Enterprise Strategic Accounts'])) {
            $teams['Enterprise Strategic Accounts']->update(['manager_id' => $sarah->id]);
        }

        // 3. Sales Reps
        $repsData = [
            [
                'name' => 'Alex Rivera',
                'email' => 'alex.rep@saleserp.com',
                'territory' => 'US West & Silicon Valley',
                'team' => 'Enterprise Strategic Accounts',
                'phone' => '+1 (555) 010-0010',
            ],
            [
                'name' => 'Elena Rostova',
                'email' => 'elena.rep@saleserp.com',
                'territory' => 'US East & Financial District',
                'team' => 'Mid-Market Velocity Team',
                'phone' => '+1 (555) 010-0011',
            ],
            [
                'name' => 'David Kim',
                'email' => 'david.rep@saleserp.com',
                'territory' => 'US West & Silicon Valley',
                'team' => 'Mid-Market Velocity Team',
                'phone' => '+1 (555) 010-0012',
            ],
            [
                'name' => 'Priya Sharma',
                'email' => 'priya.rep@saleserp.com',
                'territory' => 'APAC & Emerging Markets',
                'team' => 'Enterprise Strategic Accounts',
                'phone' => '+65 6789 0123',
            ],
            [
                'name' => 'Kenji Sato',
                'email' => 'kenji.rep@saleserp.com',
                'territory' => 'EMEA Hub (Europe & Middle East)',
                'team' => 'EMEA Expansion Team',
                'phone' => '+44 20 7946 0888',
            ],
        ];

        foreach ($repsData as $repData) {
            $rep = User::create([
                'name' => $repData['name'],
                'email' => $repData['email'],
                'password' => Hash::make('password123'),
                'role' => 'sales_rep',
                'territory_id' => $territories[$repData['territory']]->id ?? null,
                'team_id' => $teams[$repData['team']]->id ?? null,
                'is_active' => true,
                'phone' => $repData['phone'],
                'last_login_at' => now()->subHours(rand(1, 48)),
            ]);
            if ($repRole) $rep->roles()->attach($repRole->id);
        }
    }
}
