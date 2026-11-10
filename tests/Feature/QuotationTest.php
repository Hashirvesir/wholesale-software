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
use App\Models\Setting;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class QuotationTest extends TestCase
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

        Permission::firstOrCreate(['name' => 'view sales']);
        Permission::firstOrCreate(['name' => 'create sales']);
        Permission::firstOrCreate(['name' => 'edit sales']);
        Permission::firstOrCreate(['name' => 'delete sales']);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo(['view sales', 'create sales', 'edit sales', 'delete sales']);

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        $this->cashAccount = ChartOfAccount::firstOrCreate(['name' => 'Cash'], ['type' => 'Asset', 'status' => true]);
        $this->arAccount = ChartOfAccount::firstOrCreate(['name' => 'Accounts Receivable'], ['type' => 'Asset', 'status' => true]);
        $this->salesAccount = ChartOfAccount::firstOrCreate(['name' => 'Sales'], ['type' => 'Income', 'status' => true]);
        $this->cogsAccount = ChartOfAccount::firstOrCreate(['name' => 'COGS'], ['type' => 'Expense', 'status' => true]);
        $this->inventoryAccount = ChartOfAccount::firstOrCreate(['name' => 'Inventory'], ['type' => 'Asset', 'status' => true]);

        $this->warehouse = Warehouse::firstOrCreate(['name' => 'Main Warehouse'], ['status' => true]);

        $this->customer = Party::firstOrCreate(
            ['name' => 'Test Customer Quotation'],
            [
                'type' => 'customer',
                'phone' => '03111111111',
                'opening_balance' => 0,
                'balance_type' => 'debit',
                'status' => true,
            ]
        );

        $cat = Category::firstOrCreate(['name' => 'Hardware']);
        $unit = Unit::firstOrCreate(['name' => 'Pcs']);
        $brand = Brand::firstOrCreate(['name' => 'Generic'], ['status' => true]);

        $this->product = Product::create([
            'name' => 'Standard Drill Set',
            'sku' => 'DRILL-001',
            'barcode' => '88880001',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'brand_id' => $brand->id,
            'cost_price' => 1000,
            'sale_price' => 1500,
            'status' => true,
        ]);

        Stock::create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'qty' => 50,
        ]);

        Setting::firstOrCreate([], [
            'company_name' => 'Wholesale Depot',
            'currency_symbol' => 'Rs',
            'walk_in_customer_id' => $this->customer->id,
        ]);
    }

    public function test_quotation_index_and_create_pages_render(): void
    {
        $response = $this->actingAs($this->user)->get(route('quotations.index'));
        $response->assertStatus(200);
        $response->assertSee('Quotations / Estimates');

        $response = $this->actingAs($this->user)->get(route('quotations.create'));
        $response->assertStatus(200);
        $response->assertSee('Create Quotation / Estimate');

        // Test normal sales.create page rendering
        $salesCreateResponse = $this->actingAs($this->user)->get(route('sales.create'));
        $salesCreateResponse->assertStatus(200);
    }

    public function test_quotation_can_be_stored_without_affecting_stock_or_ledger(): void
    {
        $initialStock = Stock::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->value('qty');

        $payload = [
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'draft',
            'discount_amount' => 100,
            'note' => 'Prices valid for 7 days.',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 5,
                    'price' => 1500,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('quotations.store'), $payload);

        $this->assertDatabaseHas('quotations', [
            'party_id' => $this->customer->id,
            'discount_amount' => 100,
            'total_amount' => 7400,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('quotation_items', [
            'product_id' => $this->product->id,
            'qty' => 5,
            'price' => 1500,
            'total' => 7500,
        ]);

        // Stock MUST remain unchanged (50)
        $currentStock = Stock::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->value('qty');
        $this->assertEquals($initialStock, $currentStock);
    }

    public function test_quotation_status_can_be_updated(): void
    {
        $quotation = Quotation::create([
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'date' => now()->format('Y-m-d'),
            'discount_amount' => 0,
            'total_amount' => 3000,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->post(route('quotations.status', $quotation), [
            'status' => 'sent',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('sent', $quotation->fresh()->status);
    }

    public function test_quotation_conversion_flow_to_sale(): void
    {
        $quotation = Quotation::create([
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'date' => now()->format('Y-m-d'),
            'discount_amount' => 0,
            'total_amount' => 3000,
            'status' => 'accepted',
        ]);

        $quotation->items()->create([
            'product_id' => $this->product->id,
            'qty' => 2,
            'price' => 1500,
            'total' => 3000,
        ]);

        // 1. Convert redirect
        $convertResponse = $this->actingAs($this->user)->get(route('quotations.convert', $quotation));
        $convertResponse->assertRedirect(route('sales.create', ['from_quotation' => $quotation->id]));

        // Check sales.create page rendering with quotation pre-fill
        $salesCreateQuoteResponse = $this->actingAs($this->user)->get(route('sales.create', ['from_quotation' => $quotation->id]));
        $salesCreateQuoteResponse->assertStatus(200);
        $salesCreateQuoteResponse->assertSee('Converting Quotation');

        // 2. Sale store with quotation_id
        $salePayload = [
            'quotation_id' => $quotation->id,
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'date' => now()->format('Y-m-d'),
            'discount_amount' => 0,
            'payments' => [
                [
                    'account_id' => $this->cashAccount->id,
                    'amount' => 3000,
                ],
            ],
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 2,
                    'price' => 1500,
                ],
            ],
        ];

        $storeResponse = $this->actingAs($this->user)->post(route('sales.store'), $salePayload);
        $storeResponse->assertSessionHasNoErrors();

        // Verify quotation is marked converted
        $freshQuote = $quotation->fresh();
        $this->assertEquals('converted', $freshQuote->status);
        $this->assertNotNull($freshQuote->converted_sale_id);

        // Stock was reduced by 2
        $currentStock = Stock::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->value('qty');
        $this->assertEquals(48, $currentStock);
    }

    public function test_quotation_pdf_and_public_signed_url(): void
    {
        $quotation = Quotation::create([
            'party_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'date' => now()->format('Y-m-d'),
            'discount_amount' => 0,
            'total_amount' => 1500,
            'status' => 'draft',
        ]);

        $quotation->items()->create([
            'product_id' => $this->product->id,
            'qty' => 1,
            'price' => 1500,
            'total' => 1500,
        ]);

        // Authenticated PDF download
        $pdfResponse = $this->actingAs($this->user)->get(route('quotations.pdf', $quotation));
        $pdfResponse->assertStatus(200);

        // Unauthenticated public signed URL
        $signedUrl = URL::signedRoute('quotations.public-pdf', ['quotation' => $quotation->id]);
        $publicResponse = $this->get($signedUrl);
        $publicResponse->assertStatus(200);
    }
}
