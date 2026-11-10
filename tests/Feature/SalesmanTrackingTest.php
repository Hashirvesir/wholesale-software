<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\ChartOfAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SalesmanTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $salesman;
    protected Party $customer;
    protected Warehouse $warehouse;
    protected ChartOfAccount $cashAccount;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'view sales']);
        Permission::firstOrCreate(['name' => 'create sales']);
        Permission::firstOrCreate(['name' => 'view payments']);
        Permission::firstOrCreate(['name' => 'create payments']);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo(Permission::all());

        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);

        $this->salesman = User::factory()->create([
            'name' => 'Tariq Salesman',
            'commission_rate' => 3.00, // 3% commission
        ]);

        $this->warehouse = Warehouse::create(['name' => 'Shop 1', 'status' => true]);

        $this->cashAccount = ChartOfAccount::create([
            'name' => 'Cash in Hand',
            'type' => 'Asset',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => true,
        ]);

        $this->customer = Party::create([
            'name' => 'Gujranwala Retailers',
            'type' => 'customer',
            'phone' => '03451112233',
            'address' => 'Main Wholesale Bazaar',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => true,
        ]);
    }

    public function test_salesman_performance_report_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.salesman-performance'));
        $response->assertOk();
        $response->assertSee('Salesman');
    }

    public function test_sale_tagged_with_salesman_and_commission_calculated(): void
    {
        $sale = Sale::create([
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'salesman_id' => $this->salesman->id,
            'date' => now()->toDateString(),
            'total_amount' => 50000.00,
            'discount_amount' => 0,
            'paid_amount' => 0,
        ]);

        $payment = Payment::create([
            'party_id' => $this->customer->id,
            'from_account_id' => $this->cashAccount->id,
            'warehouse_id' => $this->warehouse->id,
            'salesman_id' => $this->salesman->id,
            'amount' => 20000.00,
            'payment_type' => 'received',
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('reports.salesman-performance', [
            'salesman_id' => $this->salesman->id,
        ]));

        $response->assertOk();
        // Check total sales
        $response->assertSee('50,000.00');
        // Check total recoveries
        $response->assertSee('20,000.00');
        // Check 3% commission on sales (1500.00)
        $response->assertSee('1,500.00');
        // Check 3% commission on recoveries (600.00)
        $response->assertSee('600.00');
    }
}
