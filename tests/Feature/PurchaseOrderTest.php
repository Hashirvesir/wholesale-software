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
use App\Models\Stock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ChartOfAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Party $vendor;
    protected Warehouse $warehouse;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'view purchases']);
        Permission::firstOrCreate(['name' => 'create purchases']);
        Permission::firstOrCreate(['name' => 'edit purchases']);
        Permission::firstOrCreate(['name' => 'delete purchases']);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo(Permission::all());

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        $this->warehouse = Warehouse::create(['name' => 'Main Warehouse', 'status' => true]);

        $this->vendor = Party::create([
            'name' => 'Cement Manufacturer Ltd',
            'type' => 'vendor',
            'phone' => '03211234567',
            'opening_balance' => 0,
            'balance_type' => 'credit',
            'status' => true,
        ]);

        $category = Category::create(['name' => 'Building Materials']);
        $unit = Unit::create(['name' => 'Bag', 'short_name' => 'bag']);
        $brand = Brand::create(['name' => 'Falcon Cement', 'status' => true]);

        $this->product = Product::create([
            'name' => 'Falcon Cement Bag 50kg',
            'sku' => 'FALCON-50KG',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'brand_id' => $brand->id,
            'sale_price' => 1500.00,
            'alert_quantity' => 20,
            'status' => true,
        ]);

        ChartOfAccount::create([
            'name' => 'Inventory',
            'type' => 'Asset',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => true,
        ]);

        ChartOfAccount::create([
            'name' => 'Accounts Payable',
            'type' => 'Liability',
            'opening_balance' => 0,
            'balance_type' => 'credit',
            'status' => true,
        ]);
    }

    public function test_purchase_order_index_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-orders.index'));
        $response->assertOk();
        $response->assertSee('Purchase Orders (PO)', false);
    }

    public function test_purchase_order_create_page_renders_with_same_layout_and_products(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-orders.create'));
        $response->assertOk();
        $response->assertSee('Create Purchase Order (PO)', false);
        $response->assertSee('poForm()', false);
        $response->assertSee($this->vendor->name, false);
        $response->assertSee($this->product->name, false);
        $response->assertSee('Destination Warehouse', false);
    }

    public function test_can_create_purchase_order_with_items(): void
    {
        $response = $this->actingAs($this->user)->post(route('purchase-orders.store'), [
            'po_number' => 'PO-2026-0001',
            'party_id' => $this->vendor->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'currency' => 'PKR',
            'exchange_rate' => 1.0,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 100,
                    'price' => 1200.00,
                ]
            ],
            'note' => 'Deliver by Friday truck',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-2026-0001',
            'party_id' => $this->vendor->id,
            'status' => 'pending',
            'total_amount' => 120000.00,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'product_id' => $this->product->id,
            'ordered_qty' => 100,
            'received_qty' => 0,
            'unit_price' => 1200.00,
        ]);
    }

    public function test_partial_receiving_goods_creates_purchase_and_updates_stock_and_status(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0002',
            'party_id' => $this->vendor->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'currency' => 'PKR',
            'exchange_rate' => 1.0,
            'total_amount' => 120000.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'ordered_qty' => 100,
            'received_qty' => 0,
            'unit_price' => 1200.00,
            'total_price' => 120000.00,
        ]);

        // Receive 40 bags (Partial Receiving)
        $response = $this->actingAs($this->user)->post(route('purchase-orders.receive', $po), [
            'receiving_date' => now()->toDateString(),
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'receive_qty' => 40,
                ]
            ],
            'note' => 'First Truck Batch',
        ]);

        $response->assertRedirect(route('purchase-orders.show', $po));

        $po->refresh();
        $poItem->refresh();

        // Status should be partial
        $this->assertEquals('partial', $po->status);
        $this->assertEquals(40, (float)$poItem->received_qty);

        // Stock in destination warehouse should be 40 with avg_cost 1200
        $stock = Stock::where('product_id', $this->product->id)->where('warehouse_id', $this->warehouse->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(40, (float)$stock->qty);
        $this->assertEquals(1200.00, (float)$stock->avg_cost);

        // Real Purchase generated
        $this->assertDatabaseHas('purchases', [
            'purchase_order_id' => $po->id,
            'party_id' => $this->vendor->id,
            'total_amount' => 48000.00,
        ]);

        // Double-entry ledger verified
        $this->assertDatabaseHas('ledger_entries', [
            'reference_type' => 'purchase',
            'party_id' => $this->vendor->id,
            'credit' => 48000.00,
        ]);

        // Now receive remaining 60 bags to complete the order
        $secondResponse = $this->actingAs($this->user)->post(route('purchase-orders.receive', $po), [
            'receiving_date' => now()->toDateString(),
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'receive_qty' => 60,
                ]
            ],
            'note' => 'Second Final Truck Batch',
        ]);

        $secondResponse->assertRedirect(route('purchase-orders.show', $po));

        $po->refresh();
        $poItem->refresh();

        // Status should now be completed
        $this->assertEquals('completed', $po->status);
        $this->assertEquals(100, (float)$poItem->received_qty);

        $stock->refresh();
        $this->assertEquals(100, (float)$stock->qty);

        // Now try receiving again on already completed PO (should cleanly warn without 500 crash)
        $thirdResponse = $this->actingAs($this->user)->post(route('purchase-orders.receive', $po), [
            'receiving_date' => now()->toDateString(),
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'receive_qty' => 10,
                ]
            ],
        ]);
        $thirdResponse->assertRedirect(route('purchase-orders.show', $po));
        $thirdResponse->assertSessionHas('warning');
    }
}
