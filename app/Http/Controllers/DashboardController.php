<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Party;
use App\Models\Warehouse;
use App\Models\RepairOrder;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth()->toDateString();

        // 1. Overview Stats (Total Lifetime)
        $totalRepairsRevenue = RepairOrder::where('status', 'delivered')->sum('total_amount');
        $stats = [
            'total_sales' => Sale::sum('total_amount') + $totalRepairsRevenue,
            'total_purchases' => Purchase::sum('total_amount'),
            'total_expenses' => Expense::sum('amount'),
            'total_customers' => Party::where('type', 'customer')->count(),
            'total_vendors' => Party::where('type', 'vendor')->count(),
            'total_products' => Product::count(),
            'total_repairs' => RepairOrder::sum('total_amount'),
            'total_repairs_count' => RepairOrder::count(),
        ];

        // 2. Today's Summary
        $todayRepairsRevenue = RepairOrder::whereDate('received_date', $today)->sum('total_amount');
        $stats['today_sales'] = Sale::where('date', $today)->sum('total_amount') + $todayRepairsRevenue;
        $stats['today_repairs'] = $todayRepairsRevenue;
        $stats['today_repairs_count'] = RepairOrder::whereDate('received_date', $today)->count();
        $stats['today_purchases'] = Purchase::where('date', $today)->sum('total_amount');
        
        $standaloneReceived = Payment::where('date', $today)->where('payment_type', 'received')->sum('amount');
        $invoiceReceived = Sale::where('date', $today)->sum('paid_amount');
        $repairCashReceivedToday = LedgerEntry::where('reference_type', 'repair')->where('date', $today)->where('debit', '>', 0)->sum('debit');
        $stats['today_received'] = $standaloneReceived + $invoiceReceived + $repairCashReceivedToday;

        $standalonePaid = Payment::where('date', $today)->where('payment_type', 'paid')->sum('amount');
        $invoicePaid = Purchase::where('date', $today)->sum('paid_amount');
        $stats['today_paid'] = $standalonePaid + $invoicePaid;

        // 3. Monthly Trend (Daily Sales & Purchases for Last 30 Days)
        $last30Days = now()->subDays(30)->toDateString();
        
        $salesTrend = Sale::where('date', '>=', $last30Days)
            ->select(DB::raw('date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
            
        $purchasesTrend = Purchase::where('date', '>=', $last30Days)
            ->select(DB::raw('date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill in missing dates with 0
        $chartData = [];
        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $chartData['dates'][] = now()->subDays($i)->format('d M');
            $chartData['sales'][] = $salesTrend->get($date)->total ?? 0;
            $chartData['purchases'][] = $purchasesTrend->get($date)->total ?? 0;
        }

        // 4. Top Selling Products (Last 30 Days)
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.date', '>=', $last30Days)
            ->select('products.name', DB::raw('SUM(sale_items.qty) as total_qty'), DB::raw('SUM(sale_items.total) as total_revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // 5. Sales by Category
        $categoryBreakdown = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.date', '>=', $last30Days)
            ->select('categories.name', DB::raw('SUM(sale_items.total) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // 6. Low Stock Products
        $lowStockThreshold = \App\Models\Setting::value('low_stock_threshold') ?? 10;
        $lowStockProducts = Product::withSum('stocks as current_stock', 'qty')
            ->having('current_stock', '<=', $lowStockThreshold) 
            ->take(5)
            ->get();

        // 7. Recent Transactions
        $recentSales = Sale::with('party')->latest()->take(5)->get();
        $recentPurchases = Purchase::with('party')->latest()->take(5)->get();

        $warehouses = Warehouse::where('status', true)->orderBy('name')->get();

        return view('dashboard', compact('stats', 'chartData', 'topProducts', 'categoryBreakdown', 'lowStockProducts', 'recentSales', 'recentPurchases', 'warehouses'));
    }
    public function quickStats(Request $request)
    {
        $data = $this->getQuickStatsData($request);

        return response()->json([
            'sales' => $data['salesToday'],
            'sales_count' => $data['salesCount'],
            'repairs' => $data['repairsToday'],
            'repairs_count' => $data['repairsCount'],
            'purchases' => $data['purchasesToday'],
            'expenses' => $data['expensesToday'],
            'total_received' => $data['totalReceivedToday'],
            'total_paid' => $data['totalPaidToday'],
            'bank_cash_details' => $data['movements'],
        ]);
    }

    public function quickStatsPdf(Request $request)
    {
        $data = $this->getQuickStatsData($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.quick_stats', $data)->setPaper('a4', 'portrait');

        $filename = 'Quick-Financial-Snapshot-' . $data['startDate'];
        if ($data['startDate'] !== $data['endDate']) {
            $filename .= '-to-' . $data['endDate'];
        }
        if ($data['warehouse']) {
            $filename .= '-' . \Illuminate\Support\Str::slug($data['warehouse']->name);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    protected function getQuickStatsData(Request $request)
    {
        $userWarehouseId = auth()->user()?->warehouse_id;
        $warehouseId = $userWarehouseId ?: $request->warehouse_id;
        $today = now()->toDateString();
        $startDate = $request->start_date ?: $today;
        $endDate   = $request->end_date   ?: $today;

        // 1. Sales
        $salesQuery = Sale::whereBetween('date', [$startDate, $endDate]);
        if ($warehouseId) {
            $salesQuery->where('warehouse_id', $warehouseId);
        }
        $salesToday = (float) $salesQuery->sum('total_amount');
        $salesCount = (int) $salesQuery->count();

        // 1b. Mobile Repairs
        $repairsQuery = RepairOrder::whereBetween(DB::raw('DATE(received_date)'), [$startDate, $endDate]);
        $repairsToday = (float) $repairsQuery->sum('total_amount');
        $repairsCount = (int) $repairsQuery->count();

        // 2. Purchases
        $purchasesQuery = Purchase::whereBetween('date', [$startDate, $endDate]);
        if ($warehouseId) {
            $purchasesQuery->where('warehouse_id', $warehouseId);
        }
        $purchasesToday = (float) $purchasesQuery->sum('total_amount');
        $purchasesCount = (int) $purchasesQuery->count();

        // 3. Expenses
        $expensesQuery = Expense::whereBetween('date', [$startDate, $endDate]);
        if ($warehouseId) {
            $expensesQuery->where('warehouse_id', $warehouseId);
        }
        $expensesToday = (float) $expensesQuery->sum('amount');
        $expensesCount = (int) $expensesQuery->count();

        // 4. Cash & Bank Status (Liquid Assets Only)
        // We include all Asset accounts except those that are clearly for stock or receivables.
        $cashBankAccounts = \App\Models\ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();

        $accountIds = $cashBankAccounts->pluck('id');

        $ledgerQuery = LedgerEntry::whereIn('account_id', $accountIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('account');

        if ($warehouseId) {
            $ledgerQuery->where('warehouse_id', $warehouseId);
        }

        $entries = $ledgerQuery->get();
        
        // Group by account for the list view
        $movements = $entries->groupBy('account.name')->map(function ($items) {
            return [
                'in' => (float) $items->sum('debit'),
                'out' => (float) $items->sum('credit'),
                'net' => (float) ($items->sum('debit') - $items->sum('credit')),
            ];
        });

        // Calculate Totals (Cash Basis)
        // For Assets (Cash/Bank): Debit is In (Received), Credit is Out (Paid).
        $totalReceivedToday = (float) $movements->sum('in');
        $totalPaidToday = (float) $movements->sum('out');
        $netFlow = $totalReceivedToday - $totalPaidToday;

        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;
        $settings = \App\Models\Setting::first();

        return compact(
            'startDate',
            'endDate',
            'warehouseId',
            'warehouse',
            'settings',
            'salesToday',
            'salesCount',
            'repairsToday',
            'repairsCount',
            'purchasesToday',
            'purchasesCount',
            'expensesToday',
            'expensesCount',
            'totalReceivedToday',
            'totalPaidToday',
            'netFlow',
            'movements'
        );
    }
}
