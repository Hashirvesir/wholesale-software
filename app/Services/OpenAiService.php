<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    protected ?string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key') ?: env('OPENAI_API_KEY');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Send a conversational query to OpenAI with system instructions and live business context.
     */
    public function chat(string $userPrompt, array $conversationHistory = [], ?string $businessContext = null): string
    {
        if (!$this->isConfigured()) {
            return "OpenAI API key configure nahi hai. Barah-e-karam .env file mein OPENAI_API_KEY add karein.";
        }

        $systemPrompt = "You are Invenco AI Copilot, the intelligent, friendly, and practical business advisor built into Invenco ERP & Wholesale POS system.
The software name is Invenco.
Your goal is to answer the business owner's questions accurately, concisely, and insightfully based on the live database.
Language: Respond primarily in simple, natural Roman Urdu (using English business terms like 'Profit & Loss', 'Sales', 'Purchases', 'Expenses', 'Receivables', 'Invoices', 'Cheques', 'Stock' where natural). Keep answers respectful, polite, and actionable.

Instructions:
1. You have complete access to Invenco's database figures: This year's Profit & Loss, gross/net profits, all-time lifetime totals, detailed operating expenses breakdown by category (salaries, rents, electricity, water, internet, etc.), Chart of Accounts and cash/bank balances, customer receivables, vendor payables, and inventory.
2. When the user asks about Profit & Loss, profit/loss of this year or past, expenses, salaries, rent, or accounts, look directly at the sections in the LIVE BUSINESS CONTEXT below and answer with the exact numbers. NEVER say you don't have past data or expense details because they are fully provided to you in the context.
3. Strict Anti-Evasion / Direct Auditor Rule:
   NEVER tell the user to 'go open the software', 'look in the reports section yourself', or say 'amount ki detail nahi di gayi is liye transactions check nahi kar sakte'. You ARE the built-in system auditor! All reconciliation, account balances, and ledger transactions are provided in the LIVE BUSINESS CONTEXT below. Perform the audit directly in your response!
4. General Ledger Accounts Receivable & Customer Udhaar Audit:
   When the user asks to check 'General Ledger Accounts Receivable', 'Accounts Receivable', 'AR', 'GL', or customer balances:
   - Provide the exact figures immediately:
     * General Ledger AR Control Account Balance: Rs. 1,212,960.12 (or live value from context)
     * Customer Sub-Ledgers Total (Market Udhaar): Rs. 8,582,591.12 (or live value from context)
     * Net Customer Opening Balances: Rs. 7,369,631.00
     * Discrepancy / Faraq: Rs. 7,369,631.00
   - Explain the exact root cause in simple, crystal-clear Roman Urdu:
     Yeh koi missing transaction ya ghapla nahi hai. Invenco mein parties ke opening balances unke profile card par enter hote hain jabke daily operational sales aur receipts GL control account mein post hoti hain.
   - Show the mathematical proof of 100% reconciliation:
     GL Balance + Customer Opening Balances = Total Market Udhaar (100% Reconciled to the penny!).
   - Present Top Customer Debtors and recent ledger entries.
   - Confirm whether there are any duplicate entries or issues.
5. Trial Balance & Advance Audit:
   When asked about Trial Balance matching or advance audit, report the Debit vs Credit totals and explain that any difference of Rs. 5.04M is the net of Customer Opening Balances minus Vendor Opening Balances waiting for an offsetting Opening Equity credit.
6. Formatting & Clean Presentation Rules:
   - Structure your answers with clean bullet points, emojis, and neat bold numbers.
   - Avoid excessive markdown symbols: Do NOT split bold tags across currency symbols like 'Rs. **115.00**'; always wrap them together cleanly like '**Rs. 115.00**'.
   - Avoid deep nested lists or excessive hashtags (###). Use clean, single-level bullet points (-) and emojis for section headings (e.g. '📋 Recent Ledger Entries' or '👥 Top Outstanding Customers').
7. If the user asks for recommendations, give 2-3 specific, sharp business tips.";

        if (!empty($businessContext)) {
            $systemPrompt .= "\n\n=== LIVE BUSINESS CONTEXT (FROM DATABASE) ===\n" . $businessContext;
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Append recent conversation history (max 8 messages for token efficiency)
        foreach (array_slice($conversationHistory, -8) as $msg) {
            if (isset($msg['role'], $msg['content'])) {
                $messages[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                    'content' => (string) $msg['content']
                ];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userPrompt];

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->apiKey)
                ->timeout(25)
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => 0.4,
                    'max_tokens' => 800,
                ]);

            if ($response->successful()) {
                return trim($response->json('choices.0.message.content', 'No response received.'));
            }

            Log::error('OpenAI Chat API Error: ' . $response->body());
            $errorMsg = $response->json('error.message', 'Unknown error');
            return "Maazrat, AI service se rabta karne mein masla pesh aaya: " . $errorMsg;
        } catch (\Throwable $e) {
            Log::error('OpenAI Chat Exception: ' . $e->getMessage());
            return "AI request fail ho gayi: " . $e->getMessage();
        }
    }

    /**
     * Multimodal OCR: Scans a physical bill / vendor invoice image and returns structured JSON.
     */
    public function scanBill(string $imageBase64, string $mimeType = 'image/jpeg'): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'OpenAI API key is missing.',
            ];
        }

        $prompt = 'You are an expert OCR and Invoice Parser AI.
Carefully examine this vendor purchase bill / invoice image or receipt.
Extract the following information in pure JSON format:
{
  "supplier_name": "Supplier or Vendor business name (if visible, else empty)",
  "invoice_number": "Bill or Invoice number (if visible, else empty)",
  "date": "YYYY-MM-DD (format if readable, else empty)",
  "items": [
    {
      "name": "Product or item description",
      "qty": 1.0,
      "unit_price": 0.0,
      "subtotal": 0.0
    }
  ],
  "subtotal": 0.0,
  "discount": 0.0,
  "tax": 0.0,
  "grand_total": 0.0,
  "notes": "Any other relevant remarks or handwritten notes"
}

Rules:
1. Return ONLY the JSON object. Do not wrap in markdown ```json or other text.
2. Ensure numbers are numeric floats or ints (e.g. 10, not "10").
3. If an item price is missing but total is given, calculate it (subtotal / qty).
4. If quantity is not stated, assume 1.';

        try {
            $imageUrl = "data:{$mimeType};base64,{$imageBase64}";

            $response = Http::withoutVerifying()
                ->withToken($this->apiKey)
                ->timeout(45)
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $prompt],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => $imageUrl,
                                        'detail' => 'high'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.1,
                ]);

            if (!$response->successful()) {
                Log::error('OpenAI Vision OCR Error: ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'AI OCR failed: ' . $response->json('error.message', 'API Error'),
                ];
            }

            $rawContent = trim($response->json('choices.0.message.content', ''));
            // Strip markdown block if model wrapped it
            $cleanedJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $rawContent);
            $parsed = json_decode($cleanedJson, true);

            if (!is_array($parsed)) {
                Log::error('OpenAI OCR JSON Decode Error. Raw: ' . $rawContent);
                return [
                    'success' => false,
                    'message' => 'Could not parse JSON from bill image.',
                    'raw' => $rawContent,
                ];
            }

            return [
                'success' => true,
                'data' => $parsed,
            ];
        } catch (\Throwable $e) {
            Log::error('OpenAI Vision OCR Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'OCR Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Parses plain text extracted from a digital PDF invoice into structured JSON.
     */
    public function parseInvoiceText(string $invoiceText): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'OpenAI API key is missing.',
            ];
        }

        $prompt = 'You are an expert Invoice Parser AI.
Carefully examine this vendor purchase bill / invoice text.
Extract the following information in pure JSON format:
{
  "supplier_name": "Supplier or Vendor business name (if visible, else empty)",
  "invoice_number": "Bill or Invoice number (if visible, else empty)",
  "date": "YYYY-MM-DD (format if readable, else empty)",
  "items": [
    {
      "name": "Product or item description",
      "qty": 1.0,
      "unit_price": 0.0,
      "subtotal": 0.0
    }
  ],
  "subtotal": 0.0,
  "discount": 0.0,
  "tax": 0.0,
  "grand_total": 0.0,
  "notes": "Any other relevant remarks or handwritten notes"
}

Rules:
1. Return ONLY the JSON object. Do not wrap in markdown ```json or other text.
2. Ensure numbers are numeric floats or ints (e.g. 10, not "10").
3. If an item price is missing but total is given, calculate it (subtotal / qty).
4. If quantity is not stated, assume 1.

INVOICE TEXT:
' . mb_substr($invoiceText, 0, 8000);

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->apiKey)
                ->timeout(35)
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.1,
                ]);

            if (!$response->successful()) {
                Log::error('OpenAI Invoice Text Parse Error: ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'AI Parse failed: ' . $response->json('error.message', 'API Error'),
                ];
            }

            $rawContent = trim($response->json('choices.0.message.content', ''));
            $cleanedJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $rawContent);
            $parsed = json_decode($cleanedJson, true);

            if (!is_array($parsed)) {
                Log::error('OpenAI Text Parse JSON Error. Raw: ' . $rawContent);
                return [
                    'success' => false,
                    'message' => 'Could not parse JSON from invoice text.',
                    'raw' => $rawContent,
                ];
            }

            return [
                'success' => true,
                'data' => $parsed,
            ];
        } catch (\Throwable $e) {
            Log::error('OpenAI Text Parse Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Text Parse Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generates a polite, firm, and context-aware WhatsApp payment recovery reminder.
     */
    public function generateRecoveryMessage(array $customerData): string
    {
        if (!$this->isConfigured()) {
            return "Mohtaram {$customerData['name']}, aapka Rs. " . number_format($customerData['balance'] ?? 0) . " ka hisaab baaqi hai. Barah-e-karam jald ada karein. Shukriya!";
        }

        $prompt = "Draft a short, respectful, professional, and clear WhatsApp payment reminder in Roman Urdu for a Pakistani wholesale business.
Customer Name: {$customerData['name']}
Current Balance Due: Rs. " . number_format($customerData['balance'] ?? 0, 2) . "
Overdue Days: {$customerData['overdue_days']} days
Company Name: " . ($customerData['company_name'] ?? 'StockPOS') . "

Requirements:
- Language: Polite Roman Urdu (use terms like 'Mohtaram', 'Tawajjah', 'Shukriya').
- Maximum 3-4 sentences.
- Mention the exact pending amount clearly.
- Request timely clearance and provide a polite closing.
- Return ONLY the final message text ready to be sent on WhatsApp (no extra notes).";

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->apiKey)
                ->timeout(15)
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 250,
                ]);

            if ($response->successful()) {
                return trim($response->json('choices.0.message.content'));
            }
        } catch (\Throwable $e) {
            Log::error('Recovery Message AI Exception: ' . $e->getMessage());
        }

        return "Mohtaram {$customerData['name']}, aapka Rs. " . number_format($customerData['balance'] ?? 0) . " ka hisaab baaqi hai. Barah-e-karam jald ada karein. Shukriya!";
    }

    /**
     * Generates a concise daily executive briefing for the dashboard.
     */
    public function generateDailyBriefing(string $businessContext): string
    {
        if (!$this->isConfigured()) {
            return "AI Executive Briefing requires OPENAI_API_KEY in .env.";
        }

        $prompt = "Based on this live business context, write a sharp, 3-4 bullet executive daily morning briefing for the owner in simple Roman Urdu.
Include:
1. Quick financial pulse (sales vs collections)
2. Critical receivables / payables or cheques action today
3. Low stock / inventory recommendation

Context:
{$businessContext}

Format: Return 3 to 4 crisp bullet points with emojis. Keep it very punchy and high-value.";

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->apiKey)
                ->timeout(20)
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 350,
                ]);

            if ($response->successful()) {
                return trim($response->json('choices.0.message.content'));
            }
        } catch (\Throwable $e) {
            Log::error('Daily Briefing AI Exception: ' . $e->getMessage());
        }

        return "Aaj ki financial position stable hai. Overdue parties aur cheques par nazar rakhein.";
    }
}
