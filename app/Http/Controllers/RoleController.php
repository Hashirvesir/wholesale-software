<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $groupedPermissions = self::getGroupedPermissions();
        return view('roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'nullable|array'
        ]);

        DB::transaction(function() use ($request) {
            $role = Role::create(['name' => $request->name]);
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }
        });

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $groupedPermissions = self::getGroupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array'
        ]);

        DB::transaction(function() use ($request, $role) {
            if ($role->name !== 'Super Admin') {
                $role->update(['name' => $request->name]);
            }
            $role->syncPermissions($request->permissions ?? []);
        });

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Super Admin role cannot be deleted.');
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * Get all permissions organized by clean logical system modules.
     */
    public static function getGroupedPermissions(): array
    {
        $allPermissions = Permission::orderBy('name')->get();

        $modules = [
            'Mobile Repairs & Lab' => [
                'icon' => '🔧',
                'description' => 'Mobile job intake, repair services, phone models, barcodes & delivery',
                'keys' => [
                    'view repairs', 'create repairs', 'edit repairs', 'delete repairs',
                    'deliver repairs', 'print repairs', 'view repair services',
                    'manage repair services', 'view repair brands', 'manage repair brands',
                    'manage repair catalog'
                ]
            ],
            'Sales & POS Counter' => [
                'icon' => '🛒',
                'description' => 'Sales invoices, thermal receipts, customer sales returns & live POS counter',
                'keys' => [
                    'view sales', 'create sales', 'edit sales', 'delete sales',
                    'return sales', 'view pos', 'create pos sale',
                    'view sales returns', 'create sales returns', 'delete sales returns'
                ]
            ],
            'Quotations & Estimates' => [
                'icon' => '📝',
                'description' => 'Customer price quotes, estimates & converting quotation into sale',
                'keys' => [
                    'view quotations', 'create quotations', 'edit quotations',
                    'delete quotations', 'convert quotations'
                ]
            ],
            'Purchases & Purchase Orders' => [
                'icon' => '📦',
                'description' => 'Vendor purchases, PO creation, goods receipt (GRN) & purchase returns',
                'keys' => [
                    'view purchases', 'create purchases', 'edit purchases', 'delete purchases',
                    'return purchases', 'view purchase orders', 'create purchase orders',
                    'edit purchase orders', 'delete purchase orders', 'receive purchase orders',
                    'view purchase returns', 'create purchase returns', 'delete purchase returns'
                ]
            ],
            'Inventory & Stock Transfers' => [
                'icon' => '🔄',
                'description' => 'Inter-warehouse transfers, stock adjustments & opening stock',
                'keys' => [
                    'view stock transfers', 'create stock transfers', 'delete stock transfers',
                    'view stock adjustments', 'create stock adjustments', 'delete stock adjustments',
                    'view opening stocks', 'manage opening stocks'
                ]
            ],
            'Finance, Accounts & Cheques' => [
                'icon' => '💰',
                'description' => 'Chart of accounts, payments, PDC cheques, expenses & account transfers',
                'keys' => [
                    'view chart of accounts', 'manage chart of accounts',
                    'view payments', 'create payments', 'delete payments',
                    'view expenses', 'manage expenses', 'manage expense categories',
                    'view account transfers', 'create account transfers', 'delete account transfers',
                    'view cheques', 'manage cheques', 'view cargo charges', 'create cargo charges'
                ]
            ],
            'Masters & Catalog' => [
                'icon' => '🏷️',
                'description' => 'Products catalog, barcode printing, categories, brands, units, warehouses & parties',
                'keys' => [
                    'view products', 'manage products', 'print barcodes',
                    'view categories', 'manage categories', 'view brands', 'manage brands',
                    'view units', 'manage units', 'view warehouses', 'manage warehouses',
                    'view parties', 'manage parties', 'export data'
                ]
            ],
            'Reports & Ledgers' => [
                'icon' => '📊',
                'description' => 'Party & account ledgers, trial balance, P&L, balance sheet & stock analytics',
                'keys' => [
                    'view account ledger', 'view party ledger', 'view trial balance',
                    'view profit loss', 'view balance sheet', 'view dead stock',
                    'view stock alert', 'view products summary', 'view stock history',
                    'view product profit loss', 'view aging report', 'view daily collection report',
                    'view expense report', 'view sales analysis report', 'view returns summary',
                    'view cargo charges report', 'view salesman performance report'
                ]
            ],
            'System, AI & Administration' => [
                'icon' => '⚙️',
                'description' => 'System settings, user accounts, roles, AI intelligence & audit logs',
                'keys' => [
                    'view settings', 'manage settings', 'download backups',
                    'view activity logs', 'manage users', 'manage roles',
                    'access ai assistant', 'access offline mode'
                ]
            ],
        ];

        $grouped = [];
        $handledNames = [];

        foreach ($modules as $groupName => $meta) {
            $perms = $allPermissions->whereIn('name', $meta['keys']);
            if ($perms->isNotEmpty()) {
                $grouped[$groupName] = [
                    'icon' => $meta['icon'],
                    'description' => $meta['description'],
                    'permissions' => $perms
                ];
                foreach ($perms as $p) {
                    $handledNames[$p->name] = true;
                }
            }
        }

        $remaining = $allPermissions->reject(fn($p) => isset($handledNames[$p->name]));
        if ($remaining->isNotEmpty()) {
            $grouped['Other Permissions'] = [
                'icon' => '✨',
                'description' => 'Custom and additional system permissions',
                'permissions' => $remaining
            ];
        }

        return $grouped;
    }
}
