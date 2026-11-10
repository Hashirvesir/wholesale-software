<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\OpeningStock;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductOpeningStockTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;
    protected Brand $brand;
    protected Unit $unit;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('manage products');
        Permission::findOrCreate('view products');

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['manage products', 'view products']);

        $this->category = Category::create(['name' => 'General Category']);
        $this->brand = Brand::create(['name' => 'General Brand']);
        $this->unit = Unit::create(['name' => 'Piece', 'short_name' => 'pc']);
        $this->warehouse = Warehouse::create(['name' => 'Main Warehouse', 'address' => 'Center']);

        ChartOfAccount::create(['name' => 'Inventory', 'type' => 'Asset', 'status' => true]);
        ChartOfAccount::create(['name' => 'Opening Balance Equity', 'type' => 'Equity', 'status' => true]);
    }

    public function test_product_create_page_renders_with_warehouses(): void
    {
        $response = $this->actingAs($this->user)->get(route('products.create'));

        $response->assertOk();
        $response->assertSee('Initial Opening Stock');
        $response->assertSee('Main Warehouse');
    }

    public function test_can_create_product_with_initial_opening_stock(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'name' => 'Samsung S24 Ultra Glass',
            'sku' => 'S24-GLASS-001',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'unit_id' => $this->unit->id,
            'sale_price' => 350.00,
            'alert_quantity' => 5,
            'status' => 1,
            'has_opening_stock' => 1,
            'opening_stock_qty' => 100,
            'opening_stock_warehouse_id' => $this->warehouse->id,
            'opening_stock_rate' => 200.00,
            'opening_stock_date' => now()->toDateString(),
            'opening_stock_note' => 'Initial batch from factory',
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'S24-GLASS-001')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Samsung S24 Ultra Glass', $product->name);

        // Check OpeningStock
        $openingStock = OpeningStock::where('product_id', $product->id)->first();
        $this->assertNotNull($openingStock);
        $this->assertEquals(100, $openingStock->qty);
        $this->assertEquals(200, $openingStock->rate);
        $this->assertEquals(20000, $openingStock->total);

        // Check Stock
        $stock = Stock::where('product_id', $product->id)->where('warehouse_id', $this->warehouse->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(100, $stock->qty);
        $this->assertEquals(200, $stock->avg_cost);
    }

    public function test_opening_stock_rate_falls_back_to_sale_price_and_shows_valuation(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'name' => 'Redmi Note 13 Protector',
            'sku' => 'REDMI-PROT-01',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'unit_id' => $this->unit->id,
            'sale_price' => 150.00,
            'alert_quantity' => 10,
            'status' => 1,
            'opening_stock_qty' => 50,
            // rate left empty/0
            'opening_stock_rate' => 0,
            'opening_stock_warehouse_id' => $this->warehouse->id,
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'REDMI-PROT-01')->first();
        $this->assertNotNull($product);

        $this->assertEquals(50, $product->getCurrentStock());
        // Fallback rate = 150.00, total valuation = 50 * 150 = 7500
        $this->assertEquals(7500.00, $product->getTotalStockValue());

        // Verify index page displays valuation
        $indexResponse = $this->actingAs($this->user)->get(route('products.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Val: Rs. 7,500.00');

        // Verify show page displays opening stock table
        $showResponse = $this->actingAs($this->user)->get(route('products.show', $product));
        $showResponse->assertOk();
        $showResponse->assertSee('Initial Opening Stock Entries');
        $showResponse->assertSee('7,500.00');
    }
}
