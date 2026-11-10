<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Cheque;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AiContextProvider
{
    /**
     * Build a rich text representation of the store's financial, operational, and historical state.
     * Optionally takes the user prompt to dynamically pull targeted data for specific parties, products, or periods.
     */
    public function getLiveContext(?string $userPrompt = null): string
    {
        $settings = Setting::first();
        $currency = $settings->currency_symbol ?? 'Rs';
        $companyName = $settings->company_name ?? 'Invenco Store';

        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $startOfYear = Carbon::now()->startOfYear()->toDateString();
        $currentYear = Carbon::now()->year;

        // 1. Today's Activity
        $todaySales = Sale::whereDate('date', $today)->get();
        $todaySalesCount = $todaySales->count();
        $todaySalesTotal = (float) $todaySales->sum('total_amount');
        $todaySalesPaid = (float) $todaySales->sum('paid_amount');

        $todayPurchases = Purchase::whereDate('date', $today)->get();
        $todayPurchasesCount = $todayPurchases->count();
        $todayPurchasesTotal = (float) $todayPurchases->sum('total_amount');

        $todayReceived = (float) Payment::where('payment_type', 'received')->whereDate('date', $today)->sum('amount');
        $todayPaid = (float) Payment::where('payment_type', 'paid')->whereDate('date', $today)->sum('amount');
        $todayExpenses = (float) Expense::whereDate('date', $today)->sum('amount');

        // 2. This Month's Activity
        $monthSalesTotal = (float) Sale::whereBetween('date', [$startOfMonth, $today])->sum('total_amount');
        $monthPurchasesTotal = (float) Purchase::whereBetween('date', [$startOfMonth, $today])->sum('total_amount');
        $monthExpensesTotal = (float) Expense::whereBetween('date', [$startOfMonth, $today])->sum('amount');
        $monthEstimatedGrossProfit = $monthSalesTotal - $monthPurchasesTotal;
        $monthEstimatedNetProfit = $monthEstimatedGrossProfit - $monthExpensesTotal;

        // 3. This Year's Activity & Profit and Loss (2026)
        $yearSalesCount = Sale::whereBetween('date', [$startOfYear, $today])->count();
        $yearSalesTotal = (float) Sale::whereBetween('date', [$startOfYear, $today])->sum('total_amount');
        $yearPurchasesCount = Purchase::whereBetween('date', [$startOfYear, $today])->count();
        $yearPurchasesTotal = (float) Purchase::whereBetween('date', [$startOfYear, $today])->sum('total_amount');
        $yearExpensesTotal = (float) Expense::whereBetween('date', [$startOfYear, $today])->sum('amount');

        $yearGrossProfit = $yearSalesTotal - $yearPurchasesTotal;
        $yearNetProfit = $yearGrossProfit - $yearExpensesTotal;
        $yearMarginPercent = $yearSalesTotal > 0 ? round(($yearNetProfit / $yearSalesTotal) * 100, 2) : 0;

        // 4. All-Time Historical Lifetime Totals
        $lifetimeSalesCount = Sale::count();
        $lifetimeSalesTotal = (float) Sale::sum('total_amount');
        $lifetimePurchasesTotal = (float) Purchase::sum('total_amount');
        $lifetimeExpensesTotal = (float) Expense::sum('amount');
        $lifetimeNetDifference = $lifetimeSalesTotal - $lifetimePurchasesTotal - $lifetimeExpensesTotal;

        // 5. Expense Breakdown by Category
        $expenseCategories = Expense::join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total_spent'), DB::raw('COUNT(expenses.id) as count'))
            ->groupBy('expense_categories.name')
            ->orderByDesc('total_spent')
            ->get();

        // 6. Chart of Accounts & Cash/Bank Balances
        $accounts = ChartOfAccount::where('status', true)->get();
        $accountBalances = [];
        foreach ($accounts as $acc) {
            $bal = $acc->getBalance();
            $accountBalances[] = [
                'name' => $acc->name,
                'type' => $acc->type,
                'balance' => $bal,
            ];
        }

        // 7. Parties Balances (Receivables & Payables)
        $parties = Party::where('status', true)->get();
        $receivables = [];
        $payables = [];
        $totalReceivableAmount = 0;
        $totalPayableAmount = 0;

        foreach ($parties as $party) {
            $bal = $party->getCurrentBalance();
            if ($bal > 10) {
                $totalReceivableAmount += $bal;
                $receivables[] = [
                    'name' => $party->name,
                    'phone' => $party->phone,
                    'balance' => $bal,
                    'credit_days' => $party->credit_days ?? 0,
                    'credit_limit' => $party->credit_limit ?? 0,
                ];
            } elseif ($bal < -10) {
                $posBal = abs($bal);
                $totalPayableAmount += $posBal;
                $payables[] = [
                    'name' => $party->name,
                    'balance' => $posBal,
                ];
            }
        }

        usort($receivables, fn($a, $b) => $b['balance'] <=> $a['balance']);
        usort($payables, fn($a, $b) => $b['balance'] <=> $a['balance']);

        $topReceivables = array_slice($receivables, 0, 8);
        $topPayables = array_slice($payables, 0, 8);

        // 8. Cheques (PDC Status)
        $receivedChequesPending = Cheque::where('type', 'received')
            ->whereIn('status', ['registered', 'deposited'])
            ->get();
        $receivedChequesToday = $receivedChequesPending->where('due_date', '<=', $today);

        $issuedChequesPending = Cheque::where('type', 'issued')
            ->whereIn('status', ['registered'])
            ->get();
        $issuedChequesToday = $issuedChequesPending->where('due_date', '<=', $today);

        // 9. Stock & Top Products
        $productsWithStock = Product::with('stocks')->where('status', true)->get();
        $lowStockList = [];
        foreach ($productsWithStock as $prod) {
            $currentStock = $prod->stocks->sum('qty');
            $minAlert = $prod->min_stock ?? 5;
            if ($currentStock <= $minAlert) {
                $lowStockList[] = [
                    'name' => $prod->name,
                    'sku' => $prod->sku,
                    'stock' => $currentStock,
                    'unit' => $prod->unit->name ?? 'PC',
                ];
            }
        }
        usort($lowStockList, fn($a, $b) => $a['stock'] <=> $b['stock']);
        $topLowStock = array_slice($lowStockList, 0, 6);

        // Top 5 Selling Products (Last 30 Days)
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateString();
        $topSelling = SaleItem::select('product_id', DB::raw('SUM(qty) as total_sold'), DB::raw('SUM(total) as total_rev'))
            ->whereHas('sale', fn($q) => $q->where('date', '>=', $thirtyDaysAgo))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product')
            ->take(5)
            ->get();

        // Dynamic Querying for specific entities mentioned in user prompt
        $dynamicContext = $this->buildDynamicPromptContext($userPrompt, $currency);

        // Compile clean, comprehensive context
        $ctx = "SOFTWARE: Invenco ERP & Wholesale POS\n";
        $ctx .= "COMPANY: {$companyName} | Today: {$today} | Currency: {$currency}\n\n";

        $ctx .= "=== 1. FINANCIAL PROFIT & LOSS (THIS YEAR {$currentYear} & ALL-TIME) ===\n";
        $ctx .= "- Year {$currentYear} Gross Sales: {$currency} " . number_format($yearSalesTotal, 2) . " ({$yearSalesCount} invoices)\n";
        $ctx .= "- Year {$currentYear} Gross Purchases: {$currency} " . number_format($yearPurchasesTotal, 2) . " ({$yearPurchasesCount} orders)\n";
        $ctx .= "- Year {$currentYear} Operating Expenses: {$currency} " . number_format($yearExpensesTotal, 2) . "\n";
        $ctx .= "- Year {$currentYear} Gross Trading Profit: {$currency} " . number_format($yearGrossProfit, 2) . "\n";
        $ctx .= "- Year {$currentYear} Net Profit: {$currency} " . number_format($yearNetProfit, 2) . " (Net Margin: {$yearMarginPercent}%)\n";
        $ctx .= "- Lifetime All-Time Sales: {$currency} " . number_format($lifetimeSalesTotal, 2) . " ({$lifetimeSalesCount} invoices)\n";
        $ctx .= "- Lifetime All-Time Purchases: {$currency} " . number_format($lifetimePurchasesTotal, 2) . "\n";
        $ctx .= "- Lifetime All-Time Operating Expenses: {$currency} " . number_format($lifetimeExpensesTotal, 2) . "\n";
        $ctx .= "- This Month ({$startOfMonth} to {$today}): Sales {$currency} " . number_format($monthSalesTotal, 2) . ", Purchases {$currency} " . number_format($monthPurchasesTotal, 2) . ", Expenses {$currency} " . number_format($monthExpensesTotal, 2) . ", Est. Net Profit {$currency} " . number_format($monthEstimatedNetProfit, 2) . "\n\n";

        $ctx .= "=== 2. OPERATING EXPENSES BREAKDOWN (TOTAL: {$currency} " . number_format($lifetimeExpensesTotal, 2) . ") ===\n";
        foreach ($expenseCategories as $cat) {
            $ctx .= "- {$cat->name}: {$currency} " . number_format($cat->total_spent, 2) . " ({$cat->count} records)\n";
        }
        $ctx .= "\n";

        $ctx .= "=== 3. CHART OF ACCOUNTS & CASH/BANK BALANCES ===\n";
        foreach ($accountBalances as $acc) {
            $ctx .= "- {$acc['name']} ({$acc['type']}): {$currency} " . number_format($acc['balance'], 2) . "\n";
        }
        $ctx .= "\n";

        $ctx .= "=== 4. TODAY'S REAL-TIME ACTIVITY ({$today}) ===\n";
        $ctx .= "- Sales: {$todaySalesCount} invoices | Total: {$currency} " . number_format($todaySalesTotal, 2) . " (Counter Paid: {$currency} " . number_format($todaySalesPaid, 2) . ")\n";
        $ctx .= "- Purchases: {$todayPurchasesCount} orders | Total: {$currency} " . number_format($todayPurchasesTotal, 2) . "\n";
        $ctx .= "- Today Cash Collected: {$currency} " . number_format($todayReceived, 2) . "\n";
        $ctx .= "- Today Cash Paid: {$currency} " . number_format($todayPaid, 2) . "\n";
        $ctx .= "- Today Direct Expenses: {$currency} " . number_format($todayExpenses, 2) . "\n\n";

        $ctx .= "=== 5. MARKET RECEIVABLES (Customer Udhaar/Baaqi) ===\n";
        $ctx .= "- Total Market Receivables: {$currency} " . number_format($totalReceivableAmount, 2) . "\n";
        $ctx .= "- Top Outstanding Customers:\n";
        foreach ($topReceivables as $c) {
            $ctx .= "  * {$c['name']}: {$currency} " . number_format($c['balance'], 2) . " (Phone: {$c['phone']}, Terms: {$c['credit_days']}d)\n";
        }
        $ctx .= "\n";

        $ctx .= "=== 6. SUPPLIER PAYABLES (Vendor Dues) ===\n";
        $ctx .= "- Total Supplier Payables: {$currency} " . number_format($totalPayableAmount, 2) . "\n";
        $ctx .= "- Top Vendors Owed:\n";
        foreach ($topPayables as $v) {
            $ctx .= "  * {$v['name']}: {$currency} " . number_format($v['balance'], 2) . "\n";
        }
        $ctx .= "\n";

        $ctx .= "=== 7. CHEQUES (PDC) STATUS ===\n";
        $ctx .= "- Customer In-Hand Cheques: {$receivedChequesPending->count()} (Total: {$currency} " . number_format($receivedChequesPending->sum('amount'), 2) . ")\n";
        $ctx .= "- Customer Cheques Maturing Today: {$receivedChequesToday->count()} (Total: {$currency} " . number_format($receivedChequesToday->sum('amount'), 2) . ")\n";
        $ctx .= "- Supplier Issued Cheques Pending: {$issuedChequesPending->count()} (Total: {$currency} " . number_format($issuedChequesPending->sum('amount'), 2) . ")\n";
        $ctx .= "- Supplier Cheques Due Today: {$issuedChequesToday->count()} (Total: {$currency} " . number_format($issuedChequesToday->sum('amount'), 2) . ")\n\n";

        $ctx .= "=== 8. STOCK & PRODUCTS ===\n";
        $ctx .= "- Critical Low Stock Items:\n";
        foreach ($topLowStock as $ls) {
            $ctx .= "  * {$ls['name']} (SKU: {$ls['sku']}) - Stock: {$ls['stock']} {$ls['unit']}\n";
        }

        if ($topSelling->isNotEmpty()) {
            $ctx .= "- Top Sellers (Past 30 Days):\n";
            foreach ($topSelling as $ts) {
                $pName = $ts->product->name ?? 'Unknown';
                $ctx .= "  * {$pName}: " . number_format($ts->total_sold) . " sold ({$currency} " . number_format($ts->total_rev, 2) . ")\n";
            }
        }

        if (!empty($dynamicContext)) {
            $ctx .= "\n=== 9. SPECIFIC SEARCH RESULTS FOR USER QUERY ===\n" . $dynamicContext;
        }

        return $ctx;
    }

    /**
     * Inspects user prompt to find matching parties, products, or expense categories.
     */
    protected function buildDynamicPromptContext(?string $prompt, string $currency): string
    {
        if (empty($prompt)) return '';

        $cleanPrompt = trim($prompt);
        $words = preg_split('/[\s,\?!\.]+/u', $cleanPrompt, -1, PREG_SPLIT_NO_EMPTY);
        $filteredWords = array_filter($words, fn($w) => mb_strlen($w) >= 3 && !in_array(mb_strtolower($w), ['kya', 'hai', 'hain', 'kitna', 'kitne', 'aur', 'kaise', 'batao', 'mujhe', 'karo', 'kaun', 'konsa', 'konsi', 'saal', 'aaj']));

        $output = '';

        // 1. Check for party name matches
        foreach ($filteredWords as $word) {
            $matchedParties = Party::where('name', 'like', "%{$word}%")->take(3)->get();
            foreach ($matchedParties as $party) {
                $bal = $party->getCurrentBalance();
                $lastSale = $party->sales()->latest('date')->first();
                $output .= "- Customer/Vendor: {$party->name} (Type: {$party->type})\n";
                $output .= "  * Current Balance: {$currency} " . number_format(abs($bal), 2) . " (" . ($bal >= 0 ? 'Receivable/Dr' : 'Payable/Cr') . ")\n";
                $output .= "  * Credit Limit: {$currency} " . number_format($party->credit_limit ?? 0, 2) . " | Credit Days: {$party->credit_days} days\n";
                if ($lastSale) {
                    $output .= "  * Last Sale: Invoice #{$lastSale->id} on {$lastSale->date} for {$currency} " . number_format($lastSale->total_amount, 2) . "\n";
                }
            }
        }

        // 2. Check for product matches
        foreach ($filteredWords as $word) {
            $matchedProducts = Product::with(['stocks', 'unit'])->where('name', 'like', "%{$word}%")->orWhere('sku', 'like', "%{$word}%")->take(3)->get();
            foreach ($matchedProducts as $prod) {
                $stock = $prod->stocks->sum('qty');
                $output .= "- Product: {$prod->name} (SKU: {$prod->sku})\n";
                $output .= "  * Total Stock: {$stock} " . ($prod->unit->name ?? 'PC') . " | Sale Price: {$currency} " . number_format($prod->sale_price, 2) . " | Cost: {$currency} " . number_format($prod->purchase_price, 2) . "\n";
            }
        }

        return $output;
    }
}
