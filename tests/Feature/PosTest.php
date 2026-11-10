<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\ChartOfAccount;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PosTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Party $customer;
    protected Warehouse $warehouse;
    protected Product $product;
    protected ChartOfAccount $cashAccount;
    protected ChartOfAccount $arAccount;
    protected ChartOfAccount $salesAccount;
    protected ChartOfAccount $cogsAccount;
    protected ChartOfAccount $inventoryAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure permissions
        Permission::firstOrCreate(['name' => 'view sales']);
        Permission::firstOrCreate(['name' => 'create sales']);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo(['view sales', 'create sales']);

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        // Chart of Accounts needed
        $this->cashAccount = ChartOfAccount::firstOrCreate(['name' => 'Cash'], ['type' => 'Asset', 'status' => true]);
        $this->arAccount = ChartOfAccount::firstOrCreate(['name' => 'Accounts Receivable'], ['type' => 'Asset', 'status' => true]);
        $this->salesAccount = ChartOfAccount::firstOrCreate(['name' => 'Sales'], ['type' => 'Income', 'status' => true]);
        $this->cogsAccount = ChartOfAccount::firstOrCreate(['name' => 'COGS'], ['type' => 'Expense', 'status' => true]);
        $this->inventoryAccount = ChartOfAccount::firstOrCreate(['name' => 'Inventory'], ['type' => 'Asset', 'status' => true]);

        $this->warehouse = Warehouse::firstOrCreate(['name' => 'Main Shop Warehouse'], ['status' => true]);
        
        $this->customer = Party::firstOrCreate(
            ['name' => 'Walk-In Customer'],
            [
                'type' => 'customer',
                'phone' => '03001234567',
                'opening_balance' => 0,
                'balance_type' => 'debit',
                'status' => true
            ]
        );

        Setting::firstOrCreate([], [
            'company_name' => 'Test Company',
            'currency_symbol' => 'Rs',
            'walk_in_customer_id' => $this->customer->id,
            'shop_warehouse_id' => $this->warehouse->id,
        ]);

        $category = \App\Models\Category::firstOrCreate(['name' => 'General']);
        $unit = \App\Models\Unit::firstOrCreate(['name' => 'Piece', 'short_name' => 'pc']);
        $brand = \App\Models\Brand::firstOrCreate(['name' => 'General Brand', 'status' => true]);

        $this->product = Product::firstOrCreate(
            ['sku' => 'TEST-SKU-101'],
            [
                'name' => 'Test POS Product',
                'barcode' => '8901234567890',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'brand_id' => $brand->id,
                'sale_price' => 250,
                'status' => true,
            ]
        );

        Stock::updateOrCreate(
            ['product_id' => $this->product->id, 'warehouse_id' => $this->warehouse->id],
            ['qty' => 50, 'avg_cost' => 180]
        );
    }

    public function test_pos_screen_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('pos.index'));
        $response->assertStatus(200);
        $response->assertSee('POS Counter');
        $response->assertSee('Scan Ready');
    }

    public function test_quick_add_customer_from_pos(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('pos.quick-add-customer'), [
            'name' => 'POS Test Customer',
            'phone' => '03129876543',
            'address' => 'Market 4',
            'opening_balance' => 500,
            'balance_type' => 'debit',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'customer' => [
                'name' => 'POS Test Customer',
                'phone' => '03129876543',
            ]
        ]);

        $this->assertDatabaseHas('parties', [
            'name' => 'POS Test Customer',
            'phone' => '03129876543',
        ]);
    }

    public function test_pos_store_creates_sale_and_updates_stock_and_ledger(): void
    {
        $initialStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('qty');

        $response = $this->actingAs($this->user)->postJson(route('pos.store'), [
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 2,
                    'price' => 250,
                ]
            ],
            'discount_amount' => 50, // Subtotal 500 - 50 = 450
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'amount' => 450, // Full payment for Walk-in
                ]
            ],
            'note' => 'POS Unit Test Sale',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'sale' => [
                'total_amount' => 450,
                'paid_amount' => 450,
                'discount_amount' => 50,
            ]
        ]);

        // Stock decreased by 2
        $newStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('qty');

        $this->assertEquals($initialStock - 2, $newStock);

        // Verify Sale in database
        $this->assertDatabaseHas('sales', [
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'total_amount' => 450,
            'paid_amount' => 450,
        ]);
    }

    public function test_walk_in_customer_requires_full_payment(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('pos.store'), [
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 1,
                    'price' => 250,
                ]
            ],
            'discount_amount' => 0,
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'amount' => 100, // Less than 250
                ]
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Walk-In Customer sales must be paid in full — no credit allowed.',
        ]);
    }
}
