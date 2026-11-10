<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\Product;
use App\Models\Setting;
use App\Services\AiContextProvider;
use App\Services\OpenAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

        $businessContext = $this->contextProvider->getLiveContext();
        $reply = $this->aiService->chat($prompt, $history, $businessContext);

        return response()->json([
            'success' => true,
            'reply' => $reply,
        ]);
    }

    /**
     * Multimodal OCR: Scan vendor bill image and map to local products and suppliers.
     */
    public function scanBill(Request $request): JsonResponse
    {
        $request->validate([
            'bill_image' => 'required|file|mimes:jpeg,png,jpg,webp,pdf|max:12288',
        ]);

        if (!$this->aiService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'OpenAI API key configure nahi hai.',
            ], 422);
        }

        $file = $request->file('bill_image');
        $mime = $file->getMimeType();
        $base64 = base64_encode(file_get_contents($file->getRealPath()));

        $result = $this->aiService->scanBill($base64, $mime);

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
            $matchedSupplier = Party::whereIn('type', ['supplier', 'both'])
                ->where(function ($q) use ($sName) {
                    $q->where('name', 'like', "%{$sName}%")
                      ->orWhere('phone', 'like', "%{$sName}%");
                })->first();
        }

        // 2. Smart Product Matching for each extracted line item
        $existingProducts = Product::with(['unit', 'stocks'])->where('status', true)->get();
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

            $matchedItems[] = [
                'raw_name' => $rawName,
                'qty' => (float) ($item['qty'] ?? 1),
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'subtotal' => (float) ($item['subtotal'] ?? 0),
                'matched_product_id' => $bestMatch ? $bestMatch->id : null,
                'matched_product_name' => $bestMatch ? $bestMatch->name : null,
                'matched_product_sku' => $bestMatch ? $bestMatch->sku : null,
                'current_stock' => $bestMatch ? $bestMatch->stocks->sum('qty') : 0,
                'confidence' => $bestScore,
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
        ]);
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
