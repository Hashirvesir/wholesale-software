<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define Permissions
        $permissions = [
            // Trading
            'view sales', 'create sales', 'edit sales', 'delete sales', 'return sales',
            'view purchases', 'create purchases', 'edit purchases', 'delete purchases', 'return purchases',
            'view stock transfers', 'create stock transfers', 'delete stock transfers',
            'view stock adjustments', 'create stock adjustments', 'delete stock adjustments',
            'view cargo charges', 'create cargo charges',
            
            // Finance
            'view chart of accounts', 'manage chart of accounts',
            'view payments', 'create payments', 'delete payments',
            'view expenses', 'manage expenses',
            'view account transfers', 'create account transfers', 'delete account transfers',

            // Masters
            'view products', 'manage products',
            'view categories', 'manage categories',
            'view brands', 'manage brands',
            'view units', 'manage units',
            'view warehouses', 'manage warehouses',
            'view parties', 'manage parties',
            'view opening stocks', 'manage opening stocks',

            // Reports
            'view account ledger', 'view party ledger', 'view trial balance',
            'view profit loss', 'view balance sheet', 'view dead stock',
            'view stock alert', 'view products summary', 'view stock history', 'view product profit loss',
            'view aging report', 'view daily collection report', 'view expense report',
            'view sales analysis report', 'view returns summary', 'view cargo charges report',

            // Admin
            'view settings', 'manage settings',
            'view activity logs',
            'manage users', 'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $adminRole->syncPermissions(Permission::all());

        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $managerRole->syncPermissions([
            'view sales', 'create sales', 'edit sales', 'return sales',
            'view purchases', 'create purchases', 'edit purchases', 'return purchases',
            'view payments', 'create payments',
            'view account ledger', 'view party ledger',
            'view products', 'view parties',
        ]);

        $salesRole = Role::firstOrCreate(['name' => 'Salesman']);
        $salesRole->syncPermissions([
            'view sales', 'create sales', 'view products', 'view parties'
        ]);

    }
}
