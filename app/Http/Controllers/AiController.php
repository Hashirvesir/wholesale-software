<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Party;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Unit;
use App\Services\AiContextProvider;
use App\Services\AiUsageService;
use App\Services\OpenAiService;
use Carbon\Carbon;
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
    protected AiUsageService $usageService;

    public function __construct(
        OpenAiService $aiService,
        AiContextProvider $contextProvider,
        AiUsageService $usageService
    ) {
        $this->aiService = $aiService;
        $this->contextProvider = $contextProvider;
        $this->usageService = $usageService;
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

        if ($this->usageService->hasReachedLimit()) {
            $stats = $this->usageService->getUsageStats();
            return response()->json([
                'success' => false,
                'limit_reached' => true,
                'reply' => "⚠️ Mahana AI limit ({$stats['limit']} requests) poori ho chuki hai. Mazeed requests ke liye barah-e-karam apna package upgrade karein.",
                'error' => "⚠️ Mahana AI limit ({$stats['limit']} requests) poori ho chuki hai. Mazeed requests ke liye barah-e-karam apna package upgrade karein.",
                'usage' => $stats,
            ], 429);
        }

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

        $this->usageService->recordUsage('chat', $request->user()?->id);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'usage' => $this->usageService->getUsageStats(),
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

        if ($this->usageService->hasReachedLimit()) {
            $stats = $this->usageService->getUsageStats();
            return response()->json([
                'success' => false,
                'limit_reached' => true,
                'message' => "⚠️ Mahana AI limit ({$stats['limit']} requests) poori ho chuki hai. Mazeed bill scans ke liye barah-e-karam apna package upgrade karein.",
                'error' => "⚠️ Mahana AI limit ({$stats['limit']} requests) poori ho chuki hai. Mazeed bill scans ke liye barah-e-karam apna package upgrade karein.",
                'usage' => $stats,
            ], 429);
        }

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

        $this->usageService->recordUsage('scan_bill', $request->user()?->id);

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

                    // Partial token match
                    similar_text(mb_strtolower($rawName), mb_strtolower($prod->name), $percent);
                    if ($percent > 70 && $percent > $bestScore) {
                        $bestScore = $percent;
                        $bestMatch = $prod;
                    }
                }
            }

            $isNew = ($bestMatch === null);

            $matchedItems[] = [
                'raw_name' => $rawName,
                'matched_product_id' => $bestMatch ? $bestMatch->id : null,
                'matched_product_name' => $bestMatch ? $bestMatch->name : null,
                'matched_product_sku' => $bestMatch ? $bestMatch->sku : null,
                'is_new' => $isNew,
                'qty' => (float) ($item['qty'] ?? 1),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'subtotal' => (float) ($item['subtotal'] ?? 0),
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
            'usage' => $this->usageService->getUsageStats(),
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
                $sale = !empty($item['sale_price']) && (float) $item['sale_price'] > 0
                    ? (float) $item['sale_price']
                    : ($cost > 0 ? round($cost * 1.20, 2) : 0);

                $product = Product::create([
                    'name' => $rawName,
                    'sku' => Product::generateSku(),
                    'barcode' => Product::generateSku(),
                    'cost_price' => $cost,
                    'sale_price' => $sale,
                    'category_id' => !empty($item['category_id']) ? $item['category_id'] : ($defaultCategory->id ?? 1),
                    'brand_id' => $defaultBrand->id ?? null,
                    'unit_id' => !empty($item['unit_id']) ? $item['unit_id'] : ($defaultUnit->id ?? 1),
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

        if ($this->usageService->hasReachedLimit()) {
            $stats = $this->usageService->getUsageStats();
            return response()->json([
                'success' => false,
                'limit_reached' => true,
                'message' => "Aapki is maah ki AI request limit ({$stats['limit']} requests) poori ho chuki hai. Mazeed recovery messages ke liye barah-e-karam apna package upgrade karein.",
                'usage' => $stats,
            ], 429);
        }

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
        $this->usageService->recordUsage('recovery_message', $request->user()?->id);

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
            'usage' => $this->usageService->getUsageStats(),
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

        if (!Cache::has($cacheKey)) {
            if ($this->usageService->hasReachedLimit()) {
                $stats = $this->usageService->getUsageStats();
                return response()->json([
                    'success' => false,
                    'limit_reached' => true,
                    'briefing' => "Aapki is maah ki AI request limit ({$stats['limit']} requests) poori ho chuki hai. Mazeed requests ke liye barah-e-karam apna package upgrade karein.",
                    'usage' => $stats,
                ], 429);
            }
        }

        $briefing = Cache::remember($cacheKey, 1800, function () use ($request) {
            $context = $this->contextProvider->getLiveContext();
            $result = $this->aiService->generateDailyBriefing($context);
            $this->usageService->recordUsage('daily_briefing', $request->user()?->id);
            return $result;
        });

        return response()->json([
            'success' => true,
            'briefing' => $briefing,
            'usage' => $this->usageService->getUsageStats(),
        ]);
    }

    /**
     * Get real-time monthly AI usage stats.
     */
    public function usageStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'usage' => $this->usageService->getUsageStats(),
        ]);
    }
}
