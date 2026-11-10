<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\ChartOfAccount;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\LedgerEntry;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OfflineSyncTest extends TestCase
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

        // Permissions & Admin Role
        Permission::firstOrCreate(['name' => 'view sales']);
        Permission::firstOrCreate(['name' => 'create sales']);
        Permission::firstOrCreate(['name' => 'view payments']);
        Permission::firstOrCreate(['name' => 'create payments']);
        Permission::firstOrCreate(['name' => 'view parties']);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo(Permission::all());

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        // Chart of Accounts
        $this->cashAccount = ChartOfAccount::firstOrCreate(['name' => 'Cash in Hand'], ['type' => 'Asset', 'status' => true]);
        $this->arAccount = ChartOfAccount::firstOrCreate(['name' => 'Accounts Receivable'], ['type' => 'Asset', 'status' => true]);
        $this->salesAccount = ChartOfAccount::firstOrCreate(['name' => 'Sales'], ['type' => 'Income', 'status' => true]);
        $this->cogsAccount = ChartOfAccount::firstOrCreate(['name' => 'COGS'], ['type' => 'Expense', 'status' => true]);
        $this->inventoryAccount = ChartOfAccount::firstOrCreate(['name' => 'Inventory'], ['type' => 'Asset', 'status' => true]);

        $this->warehouse = Warehouse::firstOrCreate(['name' => 'Main Warehouse'], ['status' => true]);

        $this->customer = Party::create([
            'name' => 'Offline Wholesale Customer',
            'type' => 'customer',
            'phone' => '03001234567',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'credit_limit' => 200000.00,
            'credit_days' => 30,
            'status' => true,
        ]);

        $category = Category::firstOrCreate(['name' => 'Stationery']);
        $unit = Unit::firstOrCreate(['name' => 'Dozen', 'short_name' => 'dz']);
        $brand = Brand::firstOrCreate(['name' => 'Brand Alpha', 'status' => true]);

        $this->product = Product::create([
            'name' => 'Notebook Premium',
            'sku' => 'NOTE-PREM-01',
            'barcode' => '8901122334455',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'brand_id' => $brand->id,
            'sale_price' => 500.00,
            'purchase_price' => 350.00,
            'status' => true,
        ]);

        Stock::updateOrCreate(
            ['product_id' => $this->product->id, 'warehouse_id' => $this->warehouse->id],
            ['qty' => 50, 'avg_cost' => 350]
        );

        Setting::firstOrCreate([], [
            'shop_warehouse_id' => $this->warehouse->id,
            'walk_in_customer_id' => $this->customer->id,
            'currency_symbol' => 'Rs',
            'company_name' => 'Test Wholesaler',
        ]);
    }

    public function test_ping_endpoint_returns_online_status(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('offline.ping'));

        $response->assertOk();
        $response->assertJson([
            'status' => 'online',
        ]);
        $response->assertJsonStructure(['status', 'server_time']);
    }

    public function test_master_data_endpoint_returns_cached_datasets(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('offline.master-data'));

        $response->assertOk();
        $response->assertJsonStructure([
            'customers',
            'products',
            'warehouses',
            'payment_accounts',
            'settings',
            'dashboard_stats',
            'synced_at',
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['customers']);
        $this->assertNotEmpty($data['products']);
        $this->assertEquals('Notebook Premium', $data['products'][0]['name']);
        $this->assertEquals(500.00, $data['products'][0]['sale_price']);
    }

    public function test_sync_endpoint_successfully_creates_sale_and_updates_accounting(): void
    {
        $offlineSaleId = 'OFFLINE-SALE-TEST-999';

        $payload = [
            'sales' => [
                [
                    'offline_id' => $offlineSaleId,
                    'party_id' => $this->customer->id,
                    'warehouse_id' => $this->warehouse->id,
                    'date' => now()->toDateString(),
                    'discount_amount' => 50.00,
                    'note' => 'Offline test order',
                    'items' => [
                        [
                            'product_id' => $this->product->id,
                            'qty' => 10,
                            'price' => 500.00,
                        ],
                    ],
                    'payments' => [
                        [
                            'account_id' => $this->cashAccount->id,
                            'amount' => 2000.00,
                        ],
                    ],
                ],
            ],
            'payments' => [],
        ];

        $response = $this->actingAs($this->user)->postJson(route('offline.sync'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonCount(1, 'synced_sales');

        // Check Sale in Database
        $sale = Sale::where('offline_id', $offlineSaleId)->first();
        $this->assertNotNull($sale);
        $this->assertEquals(4950.00, (float)$sale->total_amount); // 5000 - 50 discount
        $this->assertEquals(2000.00, (float)$sale->paid_amount);

        // Check Stock Reduction (50 - 10 = 40)
        $stock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(40, $stock->qty);

        // Check Ledger Entries (Customer Debit, Sales Revenue Credit, Cash Debit)
        $entries = LedgerEntry::where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->get();
        $this->assertCount(2, $entries);
    }

    public function test_sync_endpoint_successfully_creates_offline_payment(): void
    {
        $offlinePayId = 'OFFLINE-PAY-TEST-888';

        $payload = [
            'sales' => [],
            'payments' => [
                [
                    'offline_id' => $offlinePayId,
                    'party_id' => $this->customer->id,
                    'warehouse_id' => $this->warehouse->id,
                    'from_account_id' => $this->cashAccount->id,
                    'amount' => 15000.00,
                    'payment_type' => 'received',
                    'date' => now()->toDateString(),
                    'note' => 'Offline recovery',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->postJson(route('offline.sync'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonCount(1, 'synced_payments');

        // Check Payment in DB
        $payment = Payment::where('offline_id', $offlinePayId)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(15000.00, (float)$payment->amount);
        $this->assertEquals('received', $payment->payment_type);
    }

    public function test_sync_endpoint_is_idempotent_no_duplicate_records(): void
    {
        $offlineSaleId = 'OFFLINE-SALE-IDEMPOTENT-1';

        $payload = [
            'sales' => [
                [
                    'offline_id' => $offlineSaleId,
                    'party_id' => $this->customer->id,
                    'warehouse_id' => $this->warehouse->id,
                    'date' => now()->toDateString(),
                    'discount_amount' => 0,
                    'items' => [
                        [
                            'product_id' => $this->product->id,
                            'qty' => 5,
                            'price' => 500.00,
                        ],
                    ],
                ],
            ],
            'payments' => [],
        ];

        // 1st Sync
        $res1 = $this->actingAs($this->user)->postJson(route('offline.sync'), $payload);
        $res1->assertOk();
        $this->assertEquals(1, Sale::where('offline_id', $offlineSaleId)->count());

        // 2nd Sync with exact same payload
        $res2 = $this->actingAs($this->user)->postJson(route('offline.sync'), $payload);
        $res2->assertOk();

        // Must still be exactly 1 sale, no duplicate created
        $this->assertEquals(1, Sale::where('offline_id', $offlineSaleId)->count());
        $res2->assertJson([
            'synced_sales' => [
                [
                    'offline_id' => $offlineSaleId,
                    'already_synced' => true,
                ],
            ],
        ]);
    }

    public function test_offline_fallback_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('offline'));
        $response->assertOk();
        $response->assertSee('You are Currently Offline');
        $response->assertSee('POS Counter');
    }
}
