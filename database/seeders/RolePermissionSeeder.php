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

        // Comprehensive system-wide permissions
        $permissions = [
            // Mobile Repairs & Lab (Repairs Module)
            'view repairs',
            'create repairs',
            'edit repairs',
            'delete repairs',
            'deliver repairs',
            'print repairs',
            'view repair services',
            'manage repair services',
            'view repair brands',
            'manage repair brands',
            'manage repair catalog',

            // Sales & POS Counter
            'view sales',
            'create sales',
            'edit sales',
            'delete sales',
            'return sales',
            'view pos',
            'create pos sale',
            'view sales returns',
            'create sales returns',
            'delete sales returns',

            // Quotations / Estimates
            'view quotations',
            'create quotations',
            'edit quotations',
            'delete quotations',
            'convert quotations',

            // Purchases & Purchase Orders
            'view purchases',
            'create purchases',
            'edit purchases',
            'delete purchases',
            'return purchases',
            'view purchase orders',
            'create purchase orders',
            'edit purchase orders',
            'delete purchase orders',
            'receive purchase orders',
            'view purchase returns',
            'create purchase returns',
            'delete purchase returns',

            // Inventory & Stock Management
            'view stock transfers',
            'create stock transfers',
            'delete stock transfers',
            'view stock adjustments',
            'create stock adjustments',
            'delete stock adjustments',
            'view opening stocks',
            'manage opening stocks',

            // Finance, Accounts & Cheques
            'view chart of accounts',
            'manage chart of accounts',
            'view payments',
            'create payments',
            'delete payments',
            'view expenses',
            'manage expenses',
            'manage expense categories',
            'view account transfers',
            'create account transfers',
            'delete account transfers',
            'view cheques',
            'manage cheques',
            'view cargo charges',
            'create cargo charges',

            // Masters & Product Catalog
            'view products',
            'manage products',
            'print barcodes',
            'view categories',
            'manage categories',
            'view brands',
            'manage brands',
            'view units',
            'manage units',
            'view warehouses',
            'manage warehouses',
            'view parties',
            'manage parties',
            'export data',

            // Reports & Analytics
            'view account ledger',
            'view party ledger',
            'view trial balance',
            'view profit loss',
            'view balance sheet',
            'view dead stock',
            'view stock alert',
            'view products summary',
            'view stock history',
            'view product profit loss',
            'view aging report',
            'view daily collection report',
            'view expense report',
            'view sales analysis report',
            'view returns summary',
            'view cargo charges report',
            'view salesman performance report',

            // System, AI & Administration
            'view settings',
            'manage settings',
            'download backups',
            'view activity logs',
            'manage users',
            'manage roles',
            'access ai assistant',
            'access offline mode',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 1. Super Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $adminRole->syncPermissions(Permission::all());

        // 2. Manager Role
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $managerRole->syncPermissions([
            // Repairs
            'view repairs', 'create repairs', 'edit repairs', 'deliver repairs', 'print repairs',
            'view repair services', 'view repair brands', 'manage repair catalog',
            // Sales & POS
            'view sales', 'create sales', 'edit sales', 'return sales', 'view pos', 'create pos sale',
            'view sales returns', 'create sales returns',
            // Quotations
            'view quotations', 'create quotations', 'edit quotations', 'convert quotations',
            // Purchases
            'view purchases', 'create purchases', 'edit purchases', 'return purchases',
            'view purchase orders', 'create purchase orders', 'receive purchase orders',
            // Stock
            'view stock transfers', 'create stock transfers', 'view stock adjustments', 'create stock adjustments',
            // Finance
            'view payments', 'create payments', 'view expenses', 'view cheques', 'manage cheques',
            'view account ledger', 'view party ledger',
            // Masters
            'view products', 'view categories', 'view brands', 'view units', 'view warehouses', 'view parties',
            'print barcodes', 'export data',
            // Reports
            'view daily collection report', 'view expense report', 'view sales analysis report',
        ]);

        // 3. Technician Role (Karigar / Repair Specialist)
        $techRole = Role::firstOrCreate(['name' => 'Technician']);
        $techRole->syncPermissions([
            'view repairs',
            'edit repairs',
            'deliver repairs',
            'print repairs',
            'view repair services',
            'view repair brands',
            'view products',
        ]);

        // 4. Salesman / Cashier Role
        $salesRole = Role::firstOrCreate(['name' => 'Salesman']);
        $salesRole->syncPermissions([
            'view sales', 'create sales', 'view pos', 'create pos sale',
            'view quotations', 'create quotations',
            'view products', 'view parties',
            'view repairs', 'create repairs', 'print repairs',
        ]);

        // 5. Accountant Role
        $accountantRole = Role::firstOrCreate(['name' => 'Accountant']);
        $accountantRole->syncPermissions([
            'view chart of accounts', 'manage chart of accounts',
            'view payments', 'create payments',
            'view expenses', 'manage expenses',
            'view account transfers', 'create account transfers',
            'view cheques', 'manage cheques',
            'view account ledger', 'view party ledger', 'view trial balance',
            'view profit loss', 'view balance sheet',
            'view expense report', 'view daily collection report',
        ]);
    }
}
