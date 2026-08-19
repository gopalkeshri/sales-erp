<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'dashboard' => ['view', 'export'],
            'leads' => ['view', 'create', 'update', 'delete', 'convert', 'assign'],
            'opportunities' => ['view', 'create', 'update', 'delete', 'change_stage'],
            'customers' => ['view', 'create', 'update', 'delete'],
            'contacts' => ['view', 'create', 'update', 'delete'],
            'quotes' => ['view', 'create', 'update', 'delete', 'send', 'convert'],
            'orders' => ['view', 'create', 'update', 'delete', 'change_status', 'invoice'],
            'products' => ['view', 'create', 'update', 'delete'],
            'inventory' => ['view', 'adjust', 'transfer'],
            'invoices' => ['view', 'create', 'update', 'delete', 'record_payment', 'send'],
            'commissions' => ['view', 'calculate', 'approve', 'pay'],
            'territories' => ['view', 'create', 'update', 'delete'],
            'teams' => ['view', 'create', 'update', 'delete'],
            'reports' => ['view', 'export'],
            'users' => ['view', 'create', 'update', 'delete'],
            'settings' => ['view', 'update'],
        ];

        $permissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permName = "{$module}:{$action}";
                $permissions[$permName] = Permission::firstOrCreate(
                    ['name' => $permName],
                    ['module' => $module, 'action' => $action]
                );
            }
        }

        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['description' => 'System Administrator with full permissions.']);
        $managerRole = Role::firstOrCreate(['name' => 'manager'], ['description' => 'Sales Manager overseeing territories, teams, and approvals.']);
        $repRole = Role::firstOrCreate(['name' => 'sales_rep'], ['description' => 'Sales Representative handling leads, deals, quotes, and orders.']);

        // Admin has all permissions
        $adminRole->permissions()->sync(Permission::pluck('id'));

        // Manager permissions
        $managerPerms = Permission::whereIn('module', [
            'dashboard', 'leads', 'opportunities', 'customers', 'contacts',
            'quotes', 'orders', 'products', 'inventory', 'invoices',
            'commissions', 'territories', 'teams', 'reports'
        ])->pluck('id');
        $managerRole->permissions()->sync($managerPerms);

        // Sales Rep permissions
        $repPerms = Permission::whereIn('module', [
            'dashboard', 'leads', 'opportunities', 'customers', 'contacts',
            'quotes', 'orders', 'products', 'invoices'
        ])->whereNotIn('name', ['leads:delete', 'opportunities:delete', 'orders:delete'])
          ->pluck('id');
        $repRole->permissions()->sync($repPerms);
    }
}
