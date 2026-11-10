<?php

namespace Tests\Feature;

use App\Models\Party;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\AiContextProvider;
use App\Services\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_ai_context_provider_generates_valid_text(): void
    {
        $party = Party::create([
            'name' => 'Test Customer',
            'type' => 'customer',
            'opening_balance' => 5000,
            'balance_type' => 'debit',
            'status' => true,
        ]);

        $provider = app(AiContextProvider::class);
        $context = $provider->getLiveContext();

        $this->assertIsString($context);
        $this->assertStringContainsString('TODAY\'S REAL-TIME ACTIVITY', $context);
        $this->assertStringContainsString('MARKET RECEIVABLES', $context);
        $this->assertStringContainsString('FINANCIAL PROFIT & LOSS', $context);
        $this->assertStringContainsString('Test Customer', $context);
    }

    public function test_ai_chat_endpoint_responds_successfully(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Aaj ki collection bohot achi hai. Total sales 50,000 hui hain.',
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)->postJson(route('ai.chat'), [
            'prompt' => 'Aaj ki sales ka kya scene hai?',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'reply' => 'Aaj ki collection bohot achi hai. Total sales 50,000 hui hain.',
        ]);
    }

    public function test_ai_recovery_message_endpoint(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Mohtaram Ali Traders, aapka Rs. 15,000 ka hisaab baaqi hai. Barah-e-karam jald ada karein. Shukriya!',
                        ]
                    ]
                ]
            ], 200)
        ]);

        $party = Party::create([
            'name' => 'Ali Traders',
            'type' => 'customer',
            'phone' => '03001234567',
            'opening_balance' => 15000,
            'balance_type' => 'debit',
            'status' => true,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('ai.recovery-message'), [
            'party_id' => $party->id,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'customer_name',
            'balance',
            'message',
            'whatsapp_url',
        ]);
        $this->assertStringContainsString('923001234567', $response->json('whatsapp_url'));
    }

    public function test_ai_daily_briefing_endpoint(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => '1. Aaj ki sales stable hain.\n2. 3 customers overdue hain.\n3. Stock order karein.',
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)->getJson(route('ai.daily-briefing'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertStringContainsString('Aaj ki sales stable hain', $response->json('briefing'));
    }

    public function test_ai_audit_service_reconciles_accounts_receivable(): void
    {
        $ar = \App\Models\ChartOfAccount::create([
            'name' => 'Accounts Receivable',
            'type' => 'Asset',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => true,
        ]);

        $customer = Party::create([
            'name' => 'Bilal Cloth',
            'type' => 'customer',
            'opening_balance' => 25000,
            'balance_type' => 'debit',
            'status' => true,
        ]);

        $auditService = app(\App\Services\AiAuditService::class);
        $arAudit = $auditService->auditAccountsReceivable();

        $this->assertEquals(0, $arAudit['gl_balance']);
        $this->assertEquals(25000, $arAudit['customer_subledger_total']);
        $this->assertEquals(25000, $arAudit['party_opening_net']);
        $this->assertTrue($arAudit['is_perfectly_reconciled']);
    }

    public function test_ai_context_provider_injects_audit_on_audit_prompt(): void
    {
        \App\Models\ChartOfAccount::create([
            'name' => 'Accounts Receivable',
            'type' => 'Asset',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => true,
        ]);

        $provider = app(AiContextProvider::class);
        $context = $provider->getLiveContext('**General Ledger Accounts Receivable:** is ko check karo');

        $this->assertStringContainsString('GENERAL LEDGER AUDIT: ACCOUNTS RECEIVABLE', $context);
        $this->assertStringContainsString('MATHEMATICAL RECONCILIATION', strtoupper($context));
    }

    public function test_ai_create_scanned_products_endpoint(): void
    {
        $category = \App\Models\Category::create(['name' => 'Mobile Cases']);
        $brand = \App\Models\Brand::create(['name' => 'Generic']);
        $unit = \App\Models\Unit::create(['name' => 'Piece']);

        $response = $this->actingAs($this->user)->postJson(route('ai.create-scanned-products'), [
            'products' => [
                [
                    'name' => 'Titanium Lens Protector',
                    'unit_price' => 120,
                    'sale_price' => 180,
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                ],
                [
                    'name' => 'Silicone Case Matte',
                    'unit_price' => 250,
                    'sale_price' => 350,
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                ]
            ]
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('products', ['name' => 'Titanium Lens Protector', 'sale_price' => 180]);
        $this->assertDatabaseHas('products', ['name' => 'Silicone Case Matte', 'sale_price' => 350]);
    }

    public function test_ai_scan_bill_marks_unmatched_products_as_new(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => json_encode([
                                'supplier_name' => 'China Wholesale Co',
                                'invoice_number' => 'INV-999',
                                'date' => '2026-11-10',
                                'items' => [
                                    [
                                        'name' => 'Brand New Camera Glass 16',
                                        'qty' => 50,
                                        'unit_price' => 45,
                                        'subtotal' => 2250,
                                    ]
                                ],
                                'subtotal' => 2250,
                                'discount' => 0,
                                'grand_total' => 2250,
                            ])
                        ]
                    ]
                ]
            ], 200)
        ]);

        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('bill.jpg', 600, 800);

        $response = $this->actingAs($this->user)->post(route('ai.scan-bill'), [
            'bill_image' => $fakeImage,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'items' => [
                [
                    'raw_name' => 'Brand New Camera Glass 16',
                    'is_new' => true,
                    'auto_create' => true,
                ]
            ]
        ]);
    }
}
