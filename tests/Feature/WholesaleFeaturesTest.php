<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ChartOfAccount;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class WholesaleFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Party $customer;
    protected Warehouse $warehouse;
    protected Product $product;
    protected ChartOfAccount $cashAccount;
    protected ChartOfAccount $arAccount;
    protected ChartOfAccount $salesAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // Permissions
        Permission::firstOrCreate(['name' => 'view sales']);
        Permission::firstOrCreate(['name' => 'create sales']);
        Permission::firstOrCreate(['name' => 'view parties']);
        Permission::firstOrCreate(['name' => 'manage parties']);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo(Permission::all());

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        // Chart of Accounts
        $this->cashAccount = ChartOfAccount::firstOrCreate(['name' => 'Cash'], ['type' => 'Asset', 'status' => true]);
        $this->arAccount = ChartOfAccount::firstOrCreate(['name' => 'Accounts Receivable'], ['type' => 'Asset', 'status' => true]);
        $this->salesAccount = ChartOfAccount::firstOrCreate(['name' => 'Sales'], ['type' => 'Income', 'status' => true]);

        $this->warehouse = Warehouse::firstOrCreate(['name' => 'Main Warehouse'], ['status' => true]);

        $walkIn = Party::create([
            'name' => 'Walk In Counter',
            'type' => 'customer',
            'phone' => '03000000000',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => true,
        ]);

        $this->customer = Party::create([
            'name' => 'Wholesale Dealer A',
            'type' => 'customer',
            'phone' => '03009999888',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'credit_limit' => 50000.00,
            'credit_days' => 15,
            'status' => true,
        ]);

        $category = \App\Models\Category::firstOrCreate(['name' => 'General']);
        $unit = \App\Models\Unit::firstOrCreate(['name' => 'Piece', 'short_name' => 'pc']);
        $brand = \App\Models\Brand::firstOrCreate(['name' => 'General Brand', 'status' => true]);

        $this->product = Product::create([
            'name' => 'Standard Box Item',
            'sku' => 'BOX-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'brand_id' => $brand->id,
            'sale_price' => 1200.00,
            'purchase_price' => 900.00,
            'status' => true,
        ]);

        \App\Models\Stock::updateOrCreate(
            ['product_id' => $this->product->id, 'warehouse_id' => $this->warehouse->id],
            ['qty' => 100, 'avg_cost' => 900]
        );

        Setting::firstOrCreate([], [
            'shop_warehouse_id' => $this->warehouse->id,
            'walk_in_customer_id' => $walkIn->id,
        ]);
    }

    public function test_customer_last_sold_price_endpoint_returns_not_found_when_no_sales(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('sales.customer-last-price', [
            'party_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'found' => false,
        ]);
    }

    public function test_customer_last_sold_price_endpoint_returns_last_price(): void
    {
        // Create an older sale
        $oldSale = Sale::create([
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'date' => '2026-10-01',
            'total_amount' => 1000.00,
            'paid_amount' => 1000.00,
            'status' => 'completed',
        ]);
        SaleItem::create([
            'sale_id' => $oldSale->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'price' => 1000.00,
            'total' => 1000.00,
        ]);

        // Create a newer sale with a special negotiated rate
        $newSale = Sale::create([
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'date' => '2026-11-01',
            'total_amount' => 950.00,
            'paid_amount' => 950.00,
            'status' => 'completed',
        ]);
        SaleItem::create([
            'sale_id' => $newSale->id,
            'product_id' => $this->product->id,
            'qty' => 2,
            'price' => 950.00,
            'total' => 1900.00,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('sales.customer-last-price', [
            'party_id' => $this->customer->id,
            'product_id' => $this->product->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'found' => true,
            'price' => 950.00,
            'date' => '01 Nov, 2026',
            'sale_id' => $newSale->id,
        ]);
    }

    public function test_party_model_credit_limit_and_overdue_calculations(): void
    {
        $party = $this->customer;

        $this->assertTrue($party->hasCreditLimit());
        $this->assertEquals(50000.00, $party->credit_limit);
        $this->assertEquals(15, $party->credit_days);
        $this->assertFalse($party->isCreditLimitExceeded(10000));
        $this->assertTrue($party->isCreditLimitExceeded(60000));

        // Overdue detection: unpaid invoice older than 15 days
        $this->assertFalse($party->hasOverdueInvoices());

        Sale::create([
            'party_id' => $party->id,
            'warehouse_id' => $this->warehouse->id,
            'date' => now()->subDays(20)->format('Y-m-d'),
            'total_amount' => 5000.00,
            'paid_amount' => 0.00,
            'status' => 'completed',
        ]);

        $this->assertTrue($party->hasOverdueInvoices());
        $this->assertCount(1, $party->getOverdueInvoices());
    }

    public function test_parties_store_and_update_with_credit_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('parties.store'), [
            'name' => 'New Wholesaler B',
            'type' => 'customer',
            'phone' => '03112223344',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'credit_limit' => 75000,
            'credit_days' => 30,
            'status' => 1,
        ]);

        $response->assertRedirect(route('parties.index'));

        $newParty = Party::where('name', 'New Wholesaler B')->first();
        $this->assertNotNull($newParty);
        $this->assertEquals(75000, (float)$newParty->credit_limit);
        $this->assertEquals(30, (int)$newParty->credit_days);

        // Update party
        $updateResponse = $this->actingAs($this->user)->patch(route('parties.update', $newParty), [
            'name' => 'New Wholesaler B (Updated)',
            'type' => 'customer',
            'phone' => '03112223344',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'credit_limit' => 100000,
            'credit_days' => 45,
            'status' => 1,
        ]);

        $updateResponse->assertRedirect(route('parties.index'));
        $newParty->refresh();
        $this->assertEquals(100000, (float)$newParty->credit_limit);
        $this->assertEquals(45, (int)$newParty->credit_days);
    }

    public function test_sales_create_and_pos_pages_load_with_credit_data(): void
    {
        $salesCreateResponse = $this->actingAs($this->user)->get(route('sales.create'));
        $salesCreateResponse->assertOk();
        $salesCreateResponse->assertSee('credit_limit');
        $salesCreateResponse->assertSee('credit_days');

        $posResponse = $this->actingAs($this->user)->get(route('pos.index'));
        $posResponse->assertOk();
        $posResponse->assertSee('Credit Limit');
    }
}
