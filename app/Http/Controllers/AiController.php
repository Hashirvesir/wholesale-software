<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Party;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Unit;
use App\Services\AiContextProvider;
use App\Services\OpenAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

class AiController extends Controller
{
    protected OpenAiService $aiService;
    protected AiContextProvider $contextProvider;

    public function __construct(OpenAiService $aiService, AiContextProvider $contextProvider)
    {
        $this->aiService = $aiService;
        $this->contextProvider = $contextProvider;
    }

    /**
     * Handle conversational AI queries from the Copilot widget.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:1500',
            'history' => 'nullable|array',
        ]);

        if (!$this->aiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'reply' => 'OpenAI API key configure nahi hai. Barah-e-karam .env file mein OPENAI_API_KEY set karein.',
            ]);
        }

        $prompt = $request->input('prompt');
        $history = $request->input('history', []);

        $businessContext = $this->contextProvider->getLiveContext($prompt);
        $reply = $this->aiService->chat($prompt, $history, $businessContext);

        return response()->json([
            'success' => true,
            'reply' => $reply,
        ]);
    }

    /**
     * Multimodal OCR: Scan vendor bill image or parse PDF invoice and map to local products and suppliers.
     */
    public function scanBill(Request $request): JsonResponse
    {
        $request->validate([
            'bill_image' => 'required|file|mimes:jpeg,png,jpg,webp,pdf|max:15360',
        ]);

        if (!$this->aiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'OpenAI API key configure nahi hai.',
            ], 422);
        }

        $file = $request->file('bill_image');
        $mime = $file->getMimeType();
        $ext = strtolower($file->getClientOriginalExtension());

        // Handle PDF files
        if ($mime === 'application/pdf' || $ext === 'pdf') {
            try {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($file->getRealPath());
                $text = trim($pdf->getText());

                if (empty($text)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Is PDF mein direct text nahi mila (scanned raster PDF hai). Barah-e-karam is bill ki clear photo (JPG/PNG) upload karein.',
                    ], 422);
                }

                $result = $this->aiService->parseInvoiceText($text);
            } catch (\Throwable $e) {
                Log::error('PDF Parse Error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'PDF parse karne mein masla hua: ' . $e->getMessage(),
                ], 422);
            }
        } else {
            // Image OCR via GPT-4o Vision
            $base64 = base64_encode(file_get_contents($file->getRealPath()));
            $result = $this->aiService->scanBill($base64, $mime);
        }

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Bill scan nahi ho saka.',
            ], 422);
        }

        $data = $result['data'];

        // 1. Smart Supplier Matching
        $matchedSupplier = null;
        if (!empty($data['supplier_name'])) {
            $sName = trim($data['supplier_name']);
            $matchedSupplier = Party::whereIn('type', ['supplier', 'vendor', 'both'])
                ->where(function ($q) use ($sName) {
                    $q->where('name', 'like', "%{$sName}%")
                      ->orWhere('phone', 'like', "%{$sName}%");
                })->first();
        }

        // 2. Smart Product Matching for each extracted line item
        $existingProducts = Product::with(['unit', 'stocks'])->where('status', true)->get();
        $categories = Category::all(['id', 'name']);
        $units = Unit::all(['id', 'name']);

        $defaultCatId = $categories->first()->id ?? 1;
        $defaultUnitId = $units->first()->id ?? 1;

        $matchedItems = [];

        foreach ($data['items'] ?? [] as $item) {
            $rawName = trim($item['name'] ?? '');
            $bestMatch = null;
            $bestScore = 0;

            if (!empty($rawName)) {
                foreach ($existingProducts as $prod) {
                    // Exact name or SKU match
                    if (strcasecmp($prod->name, $rawName) === 0 || strcasecmp($prod->sku ?? '', $rawName) === 0) {
                        $bestMatch = $prod;
                        $bestScore = 100;
                        break;
                    }

                    // Partial keyword match
                    similar_text(mb_strtolower($prod->name), mb_strtolower($rawName), $percent);
                    if ($percent > $bestScore && $percent >= 45) {
                        $bestScore = $percent;
                        $bestMatch = $prod;
                    }
                }
            }

            $costPrice = (float) ($item['unit_price'] ?? 0);
            $qty = (float) ($item['qty'] ?? 1);
            $subtotal = (float) ($item['subtotal'] ?? ($qty * $costPrice));
            $isNew = ($bestMatch === null);
            $suggestedSale = $costPrice > 0 ? round($costPrice * 1.25, 2) : 100.0;

            $matchedItems[] = [
                'raw_name' => $rawName,
                'qty' => $qty,
                'unit_price' => $costPrice,
                'subtotal' => $subtotal,
                'matched_product_id' => $bestMatch ? $bestMatch->id : null,
                'matched_product_name' => $bestMatch ? $bestMatch->name : null,
                'matched_product_sku' => $bestMatch ? $bestMatch->sku : null,
                'current_stock' => $bestMatch ? (float) $bestMatch->stocks->sum('qty') : 0,
                'confidence' => $bestScore,
                'is_new' => $isNew,
                'auto_create' => $isNew,
                'sale_price' => $isNew ? $suggestedSale : (float) ($bestMatch->sale_price ?? 0),
                'category_id' => $isNew ? $defaultCatId : ($bestMatch->category_id ?? $defaultCatId),
                'unit_id' => $isNew ? $defaultUnitId : ($bestMatch->unit_id ?? $defaultUnitId),
            ];
        }

        return response()->json([
            'success' => true,
            'supplier' => [
                'raw_name' => $data['supplier_name'] ?? '',
                'matched_id' => $matchedSupplier ? $matchedSupplier->id : null,
                'matched_name' => $matchedSupplier ? $matchedSupplier->name : null,
            ],
            'invoice_number' => $data['invoice_number'] ?? '',
            'date' => $data['date'] ?? date('Y-m-d'),
            'subtotal' => (float) ($data['subtotal'] ?? 0),
            'discount' => (float) ($data['discount'] ?? 0),
            'grand_total' => (float) ($data['grand_total'] ?? 0),
            'notes' => $data['notes'] ?? '',
            'items' => $matchedItems,
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    /**
     * Bulk create newly detected products from scanned bill.
     */
    public function createScannedProducts(Request $request): JsonResponse
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|max:255',
            'products.*.unit_price' => 'nullable|numeric|min:0',
            'products.*.sale_price' => 'nullable|numeric|min:0',
            'products.*.category_id' => 'nullable|integer',
            'products.*.unit_id' => 'nullable|integer',
        ]);

        $defaultCategory = Category::first();
        $defaultBrand = Brand::first();
        $defaultUnit = Unit::first();

        $created = [];

        DB::beginTransaction();
        try {
            foreach ($request->input('products') as $item) {
                $rawName = trim($item['name']);

                // If existing product with same name exists, reuse it
                $existing = Product::where('name', $rawName)->first();
                if ($existing) {
                    $created[] = [
                        'id' => $existing->id,
                        'name' => $existing->name,
                        'sku' => $existing->sku,
                        'sale_price' => (float) $existing->sale_price,
                        'cost_price' => (float) ($item['unit_price'] ?? 0),
                        'already_existed' => true,
                    ];
                    continue;
                }

                $cost = (float) ($item['unit_price'] ?? 0);
                $sale = !empty($item['sale_price']) && $item['sale_price'] > 0
                    ? (float) $item['sale_price']
                    : ($cost > 0 ? round($cost * 1.25, 2) : 100);

                // Auto-generate clean unique SKU
                $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $rawName), 0, 4));
                if (strlen($prefix) < 3) $prefix = 'PRD';
                $sku = $prefix . '-' . rand(1000, 9999);
                while (Product::where('sku', $sku)->exists()) {
                    $sku = $prefix . '-' . rand(10000, 99999);
                }

                $catId = !empty($item['category_id']) ? $item['category_id'] : ($defaultCategory->id ?? 1);
                $brandId = $defaultBrand->id ?? 1;
                $unitId = !empty($item['unit_id']) ? $item['unit_id'] : ($defaultUnit->id ?? 1);

                $product = Product::create([
                    'name' => $rawName,
                    'sku' => $sku,
                    'category_id' => $catId,
                    'brand_id' => $brandId,
                    'unit_id' => $unitId,
                    'sale_price' => $sale,
                    'wholesale_price' => $cost,
                    'retail_price' => $sale,
                    'dealer_price' => $sale,
                    'alert_quantity' => 5,
                    'status' => true,
                ]);

                $created[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'sale_price' => (float) $product->sale_price,
                    'cost_price' => $cost,
                    'already_existed' => false,
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($created) . ' products processed successfully.',
                'products' => $created,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Create Scanned Products Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Products create karne mein masla hua: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate an AI WhatsApp payment recovery reminder for a customer.
     */
    public function recoveryMessage(Request $request): JsonResponse
    {
        $request->validate([
            'party_id' => 'required|exists:parties,id',
        ]);

        $party = Party::findOrFail($request->input('party_id'));
        $balance = $party->getCurrentBalance();

        if ($balance <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Is party ka koi overdue balance nahi hai.',
            ], 422);
        }

        $settings = Setting::first();
        $companyName = $settings->company_name ?? 'StockPOS Store';

        // Approximate overdue days based on oldest unpaid invoice
        $oldestSale = $party->sales()->whereRaw('total_amount > paid_amount')->oldest('date')->first();
        $overdueDays = $oldestSale ? max(0, Carbon::parse($oldestSale->date)->diffInDays(now())) : 0;

        $customerData = [
            'name' => $party->name,
            'balance' => $balance,
            'overdue_days' => $overdueDays,
            'company_name' => $companyName,
        ];

        $message = $this->aiService->generateRecoveryMessage($customerData);

        // Format clean WhatsApp phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $party->phone ?? '');
        if (str_starts_with($cleanPhone, '03')) {
            $cleanPhone = '92' . substr($cleanPhone, 1);
        }

        $whatsappUrl = !empty($cleanPhone)
            ? 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($message)
            : null;

        return response()->json([
            'success' => true,
            'customer_name' => $party->name,
            'balance' => $balance,
            'message' => $message,
            'whatsapp_url' => $whatsappUrl,
        ]);
    }

    /**
     * Executive Daily Briefing for Dashboard.
     */
    public function dailyBriefing(Request $request): JsonResponse
    {
        if (!$this->aiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'briefing' => 'OpenAI API key configure nahi hai.',
            ]);
        }

        $cacheKey = 'stockpos_ai_daily_briefing_' . date('Y-m-d_H');
        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }
        $briefing = Cache::remember($cacheKey, 1800, function () {
            $context = $this->contextProvider->getLiveContext();
            return $this->aiService->generateDailyBriefing($context);
        });

        return response()->json([
            'success' => true,
            'briefing' => $briefing,
        ]);
    }
}
