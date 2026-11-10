<?php

namespace App\Services;

use App\Models\Cheque;
use App\Models\Party;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AiContextProvider
{
    /**
     * Build a compact text representation of the store's live financial and operational state.
     */
    public function getLiveContext(): string
    {
        $settings = Setting::first();
        $currency = $settings->currency_symbol ?? 'Rs';
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();

        // 1. Today's Sales
        $todaySales = Sale::whereDate('date', $today)->get();
        $todaySalesCount = $todaySales->count();
        $todaySalesTotal = $todaySales->sum('total_amount');
        $todaySalesPaid = $todaySales->sum('paid_amount');

        // 2. This Month's Sales
        $monthSalesTotal = Sale::whereBetween('date', [$startOfMonth, $today])->sum('total_amount');

        // 3. Today's Purchases
        $todayPurchases = Purchase::whereDate('date', $today)->get();
        $todayPurchasesCount = $todayPurchases->count();
        $todayPurchasesTotal = $todayPurchases->sum('total_amount');

        // 4. Today's Payments Received & Paid
        $todayReceived = Payment::where('payment_type', 'received')->whereDate('date', $today)->sum('amount');
        $todayPaid = Payment::where('payment_type', 'paid')->whereDate('date', $today)->sum('amount');

        // 5. Parties Balances & Receivables
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

        // Sort descending
        usort($receivables, fn($a, $b) => $b['balance'] <=> $a['balance']);
        usort($payables, fn($a, $b) => $b['balance'] <=> $a['balance']);

        $topReceivables = array_slice($receivables, 0, 5);
        $topPayables = array_slice($payables, 0, 5);

        // 6. Cheques (PDC status)
        $receivedChequesPending = Cheque::where('type', 'received')
            ->whereIn('status', ['registered', 'deposited'])
            ->get();
        $receivedChequesToday = $receivedChequesPending->where('due_date', '<=', $today);

        $issuedChequesPending = Cheque::where('type', 'issued')
            ->whereIn('status', ['registered'])
            ->get();
        $issuedChequesToday = $issuedChequesPending->where('due_date', '<=', $today);

        // 7. Low Stock Products
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
                    'price' => $prod->sale_price,
                ];
            }
        }
        usort($lowStockList, fn($a, $b) => $a['stock'] <=> $b['stock']);
        $topLowStock = array_slice($lowStockList, 0, 5);

        // 8. Top Selling Products (Past 30 Days)
        $thirtyDaysAgo = Carbon::now()->subDays(30)->toDateString();
        $topSelling = SaleItem::select('product_id', DB::raw('SUM(qty) as total_sold'), DB::raw('SUM(total) as total_rev'))
            ->whereHas('sale', fn($q) => $q->where('date', '>=', $thirtyDaysAgo))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product')
            ->take(5)
            ->get();

        // Build Markdown / Clean structured context string
        $ctx = "STORE: " . ($settings->company_name ?? 'StockPOS Store') . " | Date: {$today} | Currency: {$currency}\n\n";

        $ctx .= "--- TODAY'S ACTIVITY ---\n";
        $ctx .= "- Sales: {$todaySalesCount} invoices | Total: {$currency} " . number_format($todaySalesTotal, 2) . " (Paid at counter: {$currency} " . number_format($todaySalesPaid, 2) . ")\n";
        $ctx .= "- Purchases: {$todayPurchasesCount} orders | Total: {$currency} " . number_format($todayPurchasesTotal, 2) . "\n";
        $ctx .= "- Cash Collected (All receipts today): {$currency} " . number_format($todayReceived, 2) . "\n";
        $ctx .= "- Cash Paid Out (Expenses/Suppliers): {$currency} " . number_format($todayPaid, 2) . "\n";
        $ctx .= "- Month-to-date Sales: {$currency} " . number_format($monthSalesTotal, 2) . "\n\n";

        $ctx .= "--- RECEIVABLES & OVERDUE (Customer Baaqi) ---\n";
        $ctx .= "- Total Market Receivables: {$currency} " . number_format($totalReceivableAmount, 2) . "\n";
        if (!empty($topReceivables)) {
            $ctx .= "- Top Outstanding Customers:\n";
            foreach ($topReceivables as $c) {
                $ctx .= "  * {$c['name']}: {$currency} " . number_format($c['balance'], 2) . " (Terms: {$c['credit_days']}d)\n";
            }
        }

        $ctx .= "\n--- PAYABLES (Supplier Dues) ---\n";
        $ctx .= "- Total Supplier Payables: {$currency} " . number_format($totalPayableAmount, 2) . "\n";
        if (!empty($topPayables)) {
            $ctx .= "- Top Vendors Owed:\n";
            foreach ($topPayables as $v) {
                $ctx .= "  * {$v['name']}: {$currency} " . number_format($v['balance'], 2) . "\n";
            }
        }

        $ctx .= "\n--- CHEQUES & PDC STATUS ---\n";
        $ctx .= "- Customer Cheques In-Hand: {$receivedChequesPending->count()} cheques (Total: {$currency} " . number_format($receivedChequesPending->sum('amount'), 2) . ")\n";
        $ctx .= "- Customer Cheques Due Today/Overdue: {$receivedChequesToday->count()} (Total: {$currency} " . number_format($receivedChequesToday->sum('amount'), 2) . ")\n";
        $ctx .= "- Supplier Issued Cheques Pending: {$issuedChequesPending->count()} (Total: {$currency} " . number_format($issuedChequesPending->sum('amount'), 2) . ")\n";
        $ctx .= "- Supplier Cheques Due Today: {$issuedChequesToday->count()} (Total: {$currency} " . number_format($issuedChequesToday->sum('amount'), 2) . ")\n";

        $ctx .= "\n--- INVENTORY & STOCK ALERTS ---\n";
        $ctx .= "- Total Products in Alert / Low Stock: " . count($lowStockList) . "\n";
        if (!empty($topLowStock)) {
            $ctx .= "- Most Critical Low Stock:\n";
            foreach ($topLowStock as $ls) {
                $ctx .= "  * {$ls['name']} (SKU: {$ls['sku']}) - Stock: {$ls['stock']} {$ls['unit']}\n";
            }
        }

        if ($topSelling->isNotEmpty()) {
            $ctx .= "\n--- TOP SELLING PRODUCTS (LAST 30 DAYS) ---\n";
            foreach ($topSelling as $ts) {
                $pName = $ts->product->name ?? 'Unknown';
                $ctx .= "  * {$pName}: " . number_format($ts->total_sold) . " units sold ({$currency} " . number_format($ts->total_rev, 2) . ")\n";
            }
        }

        return $ctx;
    }
}
