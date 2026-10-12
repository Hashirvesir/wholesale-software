<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;

class ReportController extends Controller
{
    public function accountLedger(Request $request)
    {
        $accounts = \App\Models\ChartOfAccount::orderBy('name')->get();
        $warehouses = \App\Models\Warehouse::all();
        $selectedAccountId = $request->account_id;
        $selectedWarehouseId = $request->warehouse_id;
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        
        $entries = [];
        $openingBalance = 0;
        $totalDebit = 0;
        $totalCredit = 0;

        if ($selectedAccountId) {
            $account = \App\Models\ChartOfAccount::findOrFail($selectedAccountId);
            
            // 1. Calculate impact prior to start date
            $impactQuery = LedgerEntry::where('account_id', $selectedAccountId)
                ->where('date', '<', $startDate);

            if ($selectedWarehouseId) {
                $impactQuery->where('warehouse_id', $selectedWarehouseId);
            }

            $impact = $impactQuery->select(DB::raw('SUM(debit) as total_debit, SUM(credit) as total_credit'))
                ->first();
            
            $prevDebit = $impact->total_debit ?? 0;
            $prevCredit = $impact->total_credit ?? 0;

            // SPECIAL CASE: AR/AP Opening Balances are party-based, not warehouse-tagged in ledger usually.
            // If filtering by warehouse, we might miss initial party opening balances if they aren't tagged.
            // For now, if warehouse filter is active, we might ignore global party opening balances 
            // OR we assume opening balances are not warehouse specifics unless we add warehouse_id to parties (which we haven't).
            // Let's keep logic: If Warehouse selected, ONLY count ledger items. If No warehouse, add Party Opening.
            
            if (!$selectedWarehouseId) {
                if ($account->name === 'Accounts Receivable') {
                    $partyOpeningDr = Party::where('type', 'customer')->where('balance_type', 'debit')->sum('opening_balance');
                    $partyOpeningCr = Party::where('type', 'customer')->where('balance_type', 'credit')->sum('opening_balance');
                    $prevDebit += $partyOpeningDr;
                    $prevCredit += $partyOpeningCr;
                }

                if ($account->name === 'Accounts Payable') {
                    $partyOpeningDr = Party::whereIn('type', ['vendor', 'cargo'])->where('balance_type', 'debit')->sum('opening_balance');
                    $partyOpeningCr = Party::whereIn('type', ['vendor', 'cargo'])->where('balance_type', 'credit')->sum('opening_balance');
                    $prevDebit += $partyOpeningDr;
                    $prevCredit += $partyOpeningCr;
                }
            }

            // ADD: Include account's own opening balance
            if ($account->balance_type === 'debit') {
                $prevDebit += $account->opening_balance;
            } else {
                $prevCredit += $account->opening_balance;
            }

            // Calculate Opening Balance based on account type
            if (in_array($account->type, ['Asset', 'Expense'])) {
                $openingBalance = $prevDebit - $prevCredit;
            } else {
                $openingBalance = $prevCredit - $prevDebit;
            }

            // 2. Get entries for the range
            $entriesQuery = LedgerEntry::from('ledger_entries as e1')
                ->with(['party', 'warehouse'])
                ->where('e1.account_id', $selectedAccountId)
                ->whereBetween('e1.date', [$startDate, $endDate]);

            if ($selectedWarehouseId) {
                $entriesQuery->where('e1.warehouse_id', $selectedWarehouseId);
            }

            $entries = $entriesQuery->select('e1.*')
                ->addSelect([
                    'contra_account_name' => LedgerEntry::from('ledger_entries as e2')
                        ->select('chart_of_accounts.name')
                        ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'e2.account_id')
                        ->whereColumn('e2.reference_type', 'e1.reference_type')
                        ->whereColumn('e2.reference_id', 'e1.reference_id')
                        ->where('e2.account_id', '!=', $selectedAccountId)
                        ->where(function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('e1.debit', '>', 0)->whereColumn('e2.credit', 'e1.debit');
                            })->orWhere(function ($q2) {
                                $q2->where('e1.credit', '>', 0)->whereColumn('e2.debit', 'e1.credit');
                            });
                        })
                        ->limit(1)
                ])
                ->orderBy('e1.date')
                ->orderBy('e1.created_at')
                ->get();

            $totalDebit = $entries->sum('debit');
            $totalCredit = $entries->sum('credit');
        }

        $settings = Setting::first();
        $data = compact(
            'accounts',
            'warehouses',
            'selectedAccountId',
            'selectedWarehouseId',
            'startDate', 
            'endDate', 
            'entries', 
            'openingBalance',
            'totalDebit',
            'totalCredit',
            'settings'
        );

        if ($request->has('pdf')) {
            $data['settings'] = Setting::first();
            $data['account'] = \App\Models\ChartOfAccount::find($selectedAccountId);
            $pdf = Pdf::loadView('reports.pdf.account_ledger', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Account-Ledger-' . $data['account']->name . '.pdf');
        }

        return view('reports.account_ledger', $data);
    }

    public function partyLedger(Request $request)
    {
        $parties = Party::where('status', true)->orderBy('name')->get();
        $selectedPartyId = $request->party_id;
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        
        $entries = [];
        $openingBalance = 0;
        $totalDebit = 0;
        $totalCredit = 0;

        if ($selectedPartyId) {
            $party = Party::findOrFail($selectedPartyId);
            
            // 1. Base Opening Balance from Party Model
            $baseOpening = $party->opening_balance;
            if ($party->balance_type === 'credit') {
                $baseOpening = -$baseOpening;
            }

            // 2. Add impact of all ledger entries BEFORE start date
            $previousImpact = LedgerEntry::where('party_id', $selectedPartyId)
                ->where('date', '<', $startDate)
                ->select(DB::raw('SUM(debit) - SUM(credit) as net_impact'))
                ->first()
                ->net_impact ?? 0;

            $openingBalance = $baseOpening + $previousImpact;

            // 3. Get entries for the selected range
            $entries = LedgerEntry::from('ledger_entries as e1')
                ->with(['warehouse'])
                ->where('e1.party_id', $selectedPartyId)
                ->whereBetween('e1.date', [$startDate, $endDate])
                ->select('e1.*')
                ->addSelect([
                    'contra_account_name' => LedgerEntry::from('ledger_entries as e2')
                        ->select('chart_of_accounts.name')
                        ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'e2.account_id')
                        ->whereColumn('e2.reference_type', 'e1.reference_type')
                        ->whereColumn('e2.reference_id', 'e1.reference_id')
                        ->where(function($q) use ($selectedPartyId) {
                            $q->whereNull('e2.party_id')
                              ->orWhere('e2.party_id', '!=', $selectedPartyId);
                        })
                        ->where(function ($q) {
                            $q->where(function ($q2) {
                                $q2->where('e1.debit', '>', 0)->whereColumn('e2.credit', 'e1.debit');
                            })->orWhere(function ($q2) {
                                $q2->where('e1.credit', '>', 0)->whereColumn('e2.debit', 'e1.credit');
                            });
                        })
                        ->limit(1)
                ])
                ->orderBy('e1.date')
                ->orderBy('e1.created_at')
                ->get();

            $totalDebit = $entries->sum('debit');
            $totalCredit = $entries->sum('credit');
        }

        $settings = Setting::first();
        $data = compact(
            'parties', 
            'selectedPartyId', 
            'startDate', 
            'endDate', 
            'entries', 
            'openingBalance',
            'totalDebit',
            'totalCredit',
            'settings'
        );

        if ($request->has('pdf')) {
            $data['settings'] = Setting::first();
            $data['party'] = Party::find($selectedPartyId);
            $pdf = Pdf::loadView('reports.pdf.party_ledger', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Party-Ledger-' . $data['party']->name . '.pdf');
        }

        return view('reports.party_ledger', $data);
    }

    // Same as partyLedger()'s PDF branch, but reachable without login via a signed URL (see
    // routes/web.php) so a party's statement can be shared directly, e.g. over WhatsApp. The
    // signed URL is generated with pdf=1 baked into its query string, so this just delegates.
    public function publicPartyLedgerPdf(Request $request)
    {
        return $this->partyLedger($request);
    }

    public function trialBalance(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $accounts = \App\Models\ChartOfAccount::all();
        $reportData = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $impact = LedgerEntry::where('account_id', $account->id)
                ->where('date', '<=', $date)
                ->select(DB::raw('SUM(debit) as total_debit, SUM(credit) as total_credit'))
                ->first();

            $debit = $impact->total_debit ?? 0;
            $credit = $impact->total_credit ?? 0;

            // Handle Opening Balances for Party-related accounts
            if ($account->name === 'Accounts Receivable') {
                $partyOpeningDr = Party::where('type', 'customer')->where('balance_type', 'debit')->sum('opening_balance');
                $partyOpeningCr = Party::where('type', 'customer')->where('balance_type', 'credit')->sum('opening_balance');
                
                $debit += $partyOpeningDr;
                $credit += $partyOpeningCr;
            }

            if ($account->name === 'Accounts Payable') {
                $partyOpeningDr = Party::whereIn('type', ['vendor', 'cargo'])->where('balance_type', 'debit')->sum('opening_balance');
                $partyOpeningCr = Party::whereIn('type', ['vendor', 'cargo'])->where('balance_type', 'credit')->sum('opening_balance');
                
                $debit += $partyOpeningDr;
                $credit += $partyOpeningCr;
            }

            $netBalance = round($debit - $credit, 2);

            if ($netBalance != 0) {
                $reportData[] = [
                    'account_name' => $account->name,
                    'debit' => $netBalance > 0 ? $netBalance : 0,
                    'credit' => $netBalance < 0 ? abs($netBalance) : 0,
                ];

                if ($netBalance > 0) {
                    $totalDebit += $netBalance;
                } else {
                    $totalCredit += abs($netBalance);
                }
            }
        }

        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);

        $settings = Setting::first();
        $data = compact('reportData', 'totalDebit', 'totalCredit', 'date', 'settings');

        if ($request->has('pdf')) {
            $data['settings'] = Setting::first();
            $pdf = Pdf::loadView('reports.pdf.trial_balance', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Trial-Balance-' . $date . '.pdf');
        }

        return view('reports.trial_balance', $data);
    }

    public function profitAndLoss(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $warehouseId = $request->warehouse_id;
        $warehouses = \App\Models\Warehouse::all();

        // 1. Calculate Inventory Valuation (Opening & Closing) using fast bulk WAC
        $openingValuation = $this->calculateStockValue($startDate, $warehouseId);

        $closingDate = \Carbon\Carbon::parse($endDate)->addDay()->toDateString();
        $closingValuation = $this->calculateStockValue($closingDate, $warehouseId);

        // 2. Calculate Purchases Breakdown (Gross Goods + Inward Cargo - Discounts - Returns)
        $purchaseItemQuery = \App\Models\PurchaseItem::whereHas('purchase', function($q) use ($startDate, $endDate, $warehouseId) {
            $q->whereBetween('date', [$startDate, $endDate]);
            if ($warehouseId) $q->where('warehouse_id', $warehouseId);
        });
        $grossPurchases = round((float) $purchaseItemQuery->sum(DB::raw('qty * price_pkr')), 2);
        $itemCargo = round((float) $purchaseItemQuery->sum('cargo_amount'), 2);

        // Transport-mode cargo (weight x rate)
        $transportCargoQuery = \App\Models\CargoCharge::whereHas('purchase', function($q) use ($startDate, $endDate, $warehouseId) {
            $q->whereBetween('date', [$startDate, $endDate]);
            if ($warehouseId) $q->where('warehouse_id', $warehouseId);
        })->where('total_weight', '>', 0);
        $transportCargo = round((float) $transportCargoQuery->sum('total_amount'), 2);
        $totalCargoCharges = round($itemCargo + $transportCargo, 2);

        // Purchase Discounts
        $purchaseDiscountQuery = \App\Models\Purchase::whereBetween('date', [$startDate, $endDate]);
        if ($warehouseId) {
            $purchaseDiscountQuery->where('warehouse_id', $warehouseId);
        }
        $totalPurchaseDiscount = round((float) $purchaseDiscountQuery->sum('discount_amount'), 2);

        // Landed Total Purchases (Goods + Cargo - Discounts)
        $totalPurchases = round(max(0, $grossPurchases - $totalPurchaseDiscount + $totalCargoCharges), 2);

        // Purchase Returns
        $purchaseReturnQuery = \App\Models\PurchaseReturnItem::whereHas('purchaseReturn', function($q) use ($startDate, $endDate, $warehouseId) {
            $q->whereBetween('date', [$startDate, $endDate]);
            if ($warehouseId) $q->where('warehouse_id', $warehouseId);
        });
        $totalPurchaseReturn = round((float) $purchaseReturnQuery->sum(DB::raw('qty * cost')), 2);

        // Opening Stock entries added mid-period
        $midPeriodOpeningStockQuery = \App\Models\OpeningStock::whereBetween('date', [$startDate, $endDate]);
        if ($warehouseId) {
            $midPeriodOpeningStockQuery->where('warehouse_id', $warehouseId);
        }
        $midPeriodOpeningStockVal = round((float) $midPeriodOpeningStockQuery->sum('total'), 2);

        // Net Purchases Available for COGS
        $netPurchases = round($totalPurchases - $totalPurchaseReturn + $midPeriodOpeningStockVal, 2);

        // 3. Revenue from Double Entry Ledger (Sales & Sales Returns)
        $salesAccount = \App\Models\ChartOfAccount::where('name', 'Sales')->first();
        $totalSales = 0;
        if ($salesAccount) {
            $query = LedgerEntry::where('account_id', $salesAccount->id)
                ->whereBetween('date', [$startDate, $endDate]);
            if ($warehouseId) $query->where('warehouse_id', $warehouseId);
            $totalSales = round((float) $query->sum(DB::raw('credit - debit')), 2);
        }

        $salesReturnAccount = \App\Models\ChartOfAccount::where('name', 'Sales Return')->first();
        $totalSalesReturn = 0;
        if ($salesReturnAccount) {
            $query = LedgerEntry::where('account_id', $salesReturnAccount->id)
                ->whereBetween('date', [$startDate, $endDate]);
            if ($warehouseId) $query->where('warehouse_id', $warehouseId);
            $totalSalesReturn = round((float) abs($query->sum(DB::raw('debit - credit'))), 2);
        }

        $netSales = round($totalSales - $totalSalesReturn, 2);

        // 4. Calculate COGS (Reconciliation between Perpetual Ledger & Periodic Trading Account)
        $totalStock = round($openingValuation + $netPurchases, 2);
        $periodicCogs = round($totalStock - $closingValuation, 2);

        $cogsAccount = \App\Models\ChartOfAccount::where('name', 'COGS')->first();
        $ledgerCogs = 0;
        if ($cogsAccount) {
            $cogsQuery = LedgerEntry::where('account_id', $cogsAccount->id)
                ->whereBetween('date', [$startDate, $endDate]);
            if ($warehouseId) $cogsQuery->where('warehouse_id', $warehouseId);
            $ledgerCogs = round((float) $cogsQuery->sum(DB::raw('debit - credit')), 2);
        }

        // Authoritative COGS: use real-time perpetual ledger COGS if available, otherwise periodic formula
        $cogs = $ledgerCogs > 0 ? $ledgerCogs : $periodicCogs;
        $inventoryVariance = round($periodicCogs - $cogs, 2);

        $grossProfit = round($netSales - $cogs, 2);
        $grossMarginPercent = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 1) : 0;

        // 5. Operating Expenses & Other Income from Ledger
        $incomeAccounts = \App\Models\ChartOfAccount::where('type', 'Income')
            ->whereNotIn('name', ['Sales', 'Sales Return', 'Purchase Return'])
            ->get();
        $expenseAccounts = \App\Models\ChartOfAccount::where('type', 'Expense')
            ->whereNotIn('name', ['Purchase', 'COGS', 'Cargo Expense', 'Sales Return', 'Purchase Return'])
            ->get();

        $incomeData = [];
        $expenseData = [];
        $totalOtherIncome = 0;
        $totalOtherExpenses = 0;

        foreach ($incomeAccounts as $account) {
            $query = LedgerEntry::where('account_id', $account->id)
                ->whereBetween('date', [$startDate, $endDate]);
            if ($warehouseId) $query->where('warehouse_id', $warehouseId);
            $balance = round((float) $query->sum(DB::raw('credit - debit')), 2);
            if ($balance != 0) {
                $incomeData[] = ['name' => $account->name, 'amount' => $balance];
                $totalOtherIncome += $balance;
            }
        }

        foreach ($expenseAccounts as $account) {
            $query = LedgerEntry::where('account_id', $account->id)
                ->whereBetween('date', [$startDate, $endDate]);
            if ($warehouseId) $query->where('warehouse_id', $warehouseId);
            $balance = round((float) $query->sum(DB::raw('debit - credit')), 2);
            if ($balance != 0) {
                $expenseData[] = ['name' => $account->name, 'amount' => $balance];
                $totalOtherExpenses += $balance;
            }
        }

        // 6. Net Profit Calculation
        $totalIncome = round($grossProfit + $totalOtherIncome, 2);
        $totalExpense = round($totalOtherExpenses, 2);
        $netProfit = round($totalIncome - $totalExpense, 2);
        $netMarginPercent = $netSales > 0 ? round(($netProfit / $netSales) * 100, 1) : 0;

        $settings = Setting::first();
        $data = compact(
            'incomeData', 'expenseData', 'totalSales', 'totalSalesReturn', 'netSales',
            'grossPurchases', 'totalPurchaseDiscount', 'totalCargoCharges',
            'totalPurchases', 'totalPurchaseReturn', 'netPurchases', 'totalStock',
            'openingValuation', 'closingValuation', 'periodicCogs', 'ledgerCogs',
            'cogs', 'inventoryVariance', 'grossProfit', 'grossMarginPercent',
            'totalOtherIncome', 'totalOtherExpenses', 'totalIncome', 'totalExpense',
            'netProfit', 'netMarginPercent',
            'startDate', 'endDate', 'warehouses', 'warehouseId', 'settings'
        );

        if ($request->has('pdf')) {
            $data['settings'] = Setting::first();
            $pdf = Pdf::loadView('reports.pdf.profit_loss', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Profit-Loss-Statement.pdf');
        }

        return view('reports.profit_loss', $data);
    }

    public function balanceSheet(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $assetAccounts = \App\Models\ChartOfAccount::where('type', 'Asset')->get();
        $liabilityAccounts = \App\Models\ChartOfAccount::where('type', 'Liability')->get();

        $assetsData = [];
        $liabilitiesData = [];
        $totalAssets = 0;
        $totalLiabilities = 0;

        foreach ($assetAccounts as $account) {
            $impact = LedgerEntry::where('account_id', $account->id)
                ->where('date', '<=', $date)
                ->sum(DB::raw('debit - credit'));
            
            $balance = round($impact, 2);
 
            // Handle Opening Balances for AR
            if ($account->name === 'Accounts Receivable') {
                $partyOpening = Party::where('type', 'customer')->get()->map(function($p) {
                    return $p->balance_type === 'debit' ? (float)$p->opening_balance : -(float)$p->opening_balance;
                })->sum();
                $balance = round($balance + $partyOpening, 2);
            }
 
            if ($balance != 0) {
                $assetsData[] = ['name' => $account->name, 'amount' => $balance];
                $totalAssets += $balance;
            }
        }

        foreach ($liabilityAccounts as $account) {
            $impact = LedgerEntry::where('account_id', $account->id)
                ->where('date', '<=', $date)
                ->sum(DB::raw('credit - debit'));
            
            $balance = round($impact, 2);
 
            // Handle Opening Balances for AP
            if ($account->name === 'Accounts Payable') {
                $partyOpening = Party::whereIn('type', ['vendor', 'cargo'])->get()->map(function($p) {
                    return $p->balance_type === 'credit' ? (float)$p->opening_balance : -(float)$p->opening_balance;
                })->sum();
                $balance = round($balance + $partyOpening, 2);
            }
 
            if ($balance != 0) {
                $liabilitiesData[] = ['name' => $account->name, 'amount' => $balance];
                $totalLiabilities += $balance;
            }
        }

        $totalAssets = round($totalAssets, 2);
        $totalLiabilities = round($totalLiabilities, 2);
        // Equity = Assets - Liabilities
        $equity = $totalAssets - $totalLiabilities;

        $settings = Setting::first();
        $data = compact('assetsData', 'liabilitiesData', 'totalAssets', 'totalLiabilities', 'equity', 'date', 'settings');

        if ($request->has('pdf')) {
            $data['settings'] = Setting::first();
            $pdf = Pdf::loadView('reports.pdf.balance_sheet', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Balance-Sheet-' . $date . '.pdf');
        }

        return view('reports.balance_sheet', $data);
    }

    public function stockHistory(Request $request, \App\Models\Product $product)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date ?? now()->toDateString();
        
        $movements = collect();

        // 1. Opening Stocks
        $openingStocks = \App\Models\OpeningStock::where('product_id', $product->id)->get();
        foreach ($openingStocks as $os) {
            $movements->push([
                'date' => $os->date,
                'created_at' => $os->created_at,
                'type' => 'Opening Stock',
                'reference' => 'OS-' . $os->id,
                'qty_in' => (float)$os->qty,
                'qty_out' => 0,
                'rate' => (float)$os->rate,
                'rate_rmb' => 0,
                'cargo_amount' => (float)($os->cargo_amount ?? 0),
            ]);
        }

        // 2. Purchases
        $purchases = \App\Models\PurchaseItem::with('purchase.party')
            ->where('product_id', $product->id)
            ->get();
        foreach ($purchases as $pi) {
            $movements->push([
                'date' => $pi->purchase->date,
                'created_at' => $pi->created_at,
                'type' => 'Purchase',
                'reference' => 'PUR-' . $pi->purchase_id,
                'party' => $pi->purchase->party->name,
                'qty_in' => (float)$pi->qty,
                'qty_out' => 0,
                'rate' => (float)$pi->price_pkr,
                'rate_rmb' => (float)$pi->price_rmb,
                'cargo_amount' => (float)($pi->cargo_amount ?? 0),
            ]);
        }

        // 3. Sales
        $sales = \App\Models\SaleItem::where('product_id', $product->id)
            ->whereHas('sale')
            ->with('sale.party')
            ->get();
        foreach ($sales as $si) {
            $movements->push([
                'date' => $si->sale->date,
                'created_at' => $si->created_at,
                'type' => 'Sale',
                'reference' => 'SALE-' . $si->sale_id,
                'party' => $si->sale->party->name ?? 'Unknown',
                'qty_in' => 0,
                'qty_out' => (float)$si->qty,
                'rate' => (float)$si->price,
                'rate_rmb' => 0,
            ]);
        }

        // 3.1 Sales Returns (Qty In)
        $salesReturns = \App\Models\SalesReturnItem::where('product_id', $product->id)
            ->whereHas('salesReturn')
            ->with('salesReturn.party')
            ->get();
        foreach ($salesReturns as $sri) {
            $movements->push([
                'date' => $sri->salesReturn->date,
                'created_at' => $sri->created_at,
                'type' => 'Sales Return',
                'reference' => 'SR-' . $sri->sales_return_id,
                'party' => $sri->salesReturn->party->name ?? 'Unknown',
                'qty_in' => (float)$sri->qty, // Comes back to stock
                'qty_out' => 0,
                'rate' => (float)$sri->price, // Refund Price (Display only)
                'rate_rmb' => 0,
            ]);
        }

        // 3.2 Purchase Returns (Qty Out)
        $purchaseReturns = \App\Models\PurchaseReturnItem::where('product_id', $product->id)
            ->whereHas('purchaseReturn')
            ->with('purchaseReturn.party')
            ->get();
        foreach ($purchaseReturns as $pri) {
            $movements->push([
                'date' => $pri->purchaseReturn->date,
                'created_at' => $pri->created_at,
                'type' => 'Purchase Return',
                'reference' => 'PR-' . $pri->purchase_return_id,
                'party' => $pri->purchaseReturn->party->name ?? 'Unknown',
                'qty_in' => 0,
                'qty_out' => (float)$pri->qty, // Goes out of stock
                'rate' => (float)$pri->cost,
                'rate_rmb' => 0,
            ]);
        }

        // Sort by date then by creation
        $movements = $movements->sort(function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $a['created_at'] <=> $b['created_at'];
            }
            return $a['date'] <=> $b['date'];
        });

        // 4. Calculate Running Balance and Average Cost for ALL history
        $fullHistory = [];
        $runningQty = 0;
        $runningAvgCost = 0;

        foreach ($movements as $m) {
            $qtyChange = $m['qty_in'] - $m['qty_out'];
            $prevQty = $runningQty;
            $newQty = round($runningQty + $qtyChange, 4);
            
            // Calculation Rate logic
            $calcRate = $m['rate'];
            // For Sales Return, bringing items back at high Selling Price would skew Average Cost.
            // We bring them back at current Average Cost (neutral impact) or Cost (if known).
            // Using Running Average Cost ensures the AVG val doesn't jump artificially.
            if ($m['type'] === 'Sales Return') {
                $calcRate = $runningAvgCost;
            }

            if ($m['qty_in'] > 0) {
                $cargoAmt = $m['cargo_amount'] ?? 0;
                if ($newQty > 0) {
                    $runningAvgCost = round((($prevQty * $runningAvgCost) + ($m['qty_in'] * $calcRate) + $cargoAmt) / $newQty, 2);
                } else {
                    $runningAvgCost = round($calcRate + ($cargoAmt / max(1, $m['qty_in'])), 2);
                }
            }

            $runningQty = $newQty;

            $fullHistory[] = array_merge($m, [
                'running_qty' => $runningQty,
                'running_avg_cost' => $runningAvgCost,
            ]);
        }

        // 5. Filter by date if requested
        $filteredHistory = collect($fullHistory);
        if ($startDate) {
            $filteredHistory = $filteredHistory->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $filteredHistory = $filteredHistory->where('date', '<=', $endDate);
        }

        // 6. Totals for filtered set
        $totalQtyIn = $filteredHistory->sum('qty_in');
        $totalQtyOut = $filteredHistory->sum('qty_out');

        // 6.1 Final Status (End of History)
        $lastEntry = !empty($fullHistory) ? end($fullHistory) : null;
        $currentStockStatus = $lastEntry ? $lastEntry['running_qty'] : 0;
        $currentAvgCostStatus = $lastEntry ? $lastEntry['running_avg_cost'] : 0;
        $totalStockValue = $currentStockStatus * $currentAvgCostStatus;

        if ($request->has('pdf')) {
            $product->load(['category', 'unit']);
            $data = [
                'product' => $product,
                'movements' => $filteredHistory,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalQtyIn' => $totalQtyIn,
                'totalQtyOut' => $totalQtyOut,
                'currentStockStatus' => $currentStockStatus,
                'currentAvgCostStatus' => $currentAvgCostStatus,
                'totalStockValue' => $totalStockValue,
                'settings' => Setting::first()
            ];
            $pdf = Pdf::loadView('reports.pdf.stock_history', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Stock-History-' . $product->name . '.pdf');
        }

        if ($request->has('excel')) {
            $callback = function () use ($filteredHistory) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Date', 'Transaction', 'Reference', 'Party', 'Qty In', 'Qty Out', 'Rate (RMB)', 'Rate (PKR)', 'Cargo', 'Resulting Qty', 'Avg Cost (PKR)']);
                foreach ($filteredHistory as $row) {
                    fputcsv($file, [
                        $row['date'],
                        $row['type'],
                        $row['reference'] ?? '',
                        $row['party'] ?? '',
                        $row['qty_in'],
                        $row['qty_out'],
                        $row['type'] === 'Purchase' ? $row['rate_rmb'] : '',
                        $row['rate'],
                        $row['cargo_amount'] ?? 0,
                        $row['running_qty'],
                        $row['running_avg_cost'],
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="Stock-History-' . $product->name . '.csv"',
            ]);
        }

        // 7. Paginate the collection
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 15;
        $pagedData = $filteredHistory->forPage($currentPage, $perPage)->values();
        $history = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $filteredHistory->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $settings = Setting::first();
        return view('reports.stock_history', compact('product', 'history', 'startDate', 'endDate', 'totalQtyIn', 'totalQtyOut', 'currentStockStatus', 'currentAvgCostStatus', 'totalStockValue', 'settings'));
    }

    public function productProfitLoss(Request $request, \App\Models\Product $product)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date ?? now()->toDateString();
        $warehouseId = $request->warehouse_id;
        $warehouses = \App\Models\Warehouse::all();
        $settings = Setting::first();

        $movements = collect();

        // 1. Opening Stocks (landed cost — includes cargo, matching Stock History / avg_cost)
        $osQuery = \App\Models\OpeningStock::where('product_id', $product->id);
        if ($warehouseId) $osQuery->where('warehouse_id', $warehouseId);
        foreach ($osQuery->get() as $os) {
            $unitRate = $os->qty > 0 ? round($os->total / $os->qty, 4) : (float)$os->rate;
            $movements->push([
                'date' => $os->date,
                'created_at' => $os->created_at,
                'type' => 'Opening Stock',
                'qty_in' => (float)$os->qty,
                'qty_out' => 0,
                'rate' => $unitRate,
            ]);
        }

        // 2. Purchases (landed cost — includes per-item cargo, matching Stock.avg_cost exactly.
        // cargo_amount already holds the item's full cargo share — whether entered manually or
        // auto-distributed from transport weight×rate by the purchase form — so adding
        // purchase->cargoCharge->total_amount on top would double-count the same cargo charge.)
        $purQuery = \App\Models\PurchaseItem::where('product_id', $product->id)
            ->whereHas('purchase', function ($q) use ($warehouseId) {
                if ($warehouseId) $q->where('warehouse_id', $warehouseId);
            })->with(['purchase']);
        foreach ($purQuery->get() as $pi) {
            $purchase = $pi->purchase;
            $unitRate = (float) $pi->price_pkr;

            if ($pi->qty > 0 && $pi->cargo_amount > 0) {
                $unitRate += $pi->cargo_amount / $pi->qty;
            }

            $movements->push([
                'date' => $purchase->date,
                'created_at' => $pi->created_at,
                'type' => 'Purchase',
                'qty_in' => (float)$pi->qty,
                'qty_out' => 0,
                'rate' => $unitRate,
            ]);
        }

        // 3. Sales
        $saleQuery = \App\Models\SaleItem::where('product_id', $product->id)
            ->whereHas('sale', function ($q) use ($warehouseId) {
                if ($warehouseId) $q->where('warehouse_id', $warehouseId);
            })->with(['sale.party']);
        foreach ($saleQuery->get() as $si) {
            // SaleItem->total is qty*price BEFORE the sale-level discount. Prorate it by the
            // sale's actual discount ratio so revenue here matches what's really credited to
            // the Sales ledger account (Sale->total_amount = subtotal - discount_amount).
            $saleSubtotal = (float)$si->sale->total_amount + (float)$si->sale->discount_amount;
            $discountRatio = $saleSubtotal > 0 ? ((float)$si->sale->total_amount / $saleSubtotal) : 1;

            $movements->push([
                'date' => $si->sale->date,
                'created_at' => $si->created_at,
                'type' => 'Sale',
                'id' => $si->sale_id,
                'party' => $si->sale->party->name ?? 'Unknown',
                'qty_in' => 0,
                'qty_out' => (float)$si->qty,
                'price' => (float)$si->price,
                'revenue' => round((float)$si->total * $discountRatio, 2),
            ]);
        }

        // 4. Sales Returns (Negative Revenue, discount-prorated to match the sale it reverses)
        $srQuery = \App\Models\SalesReturnItem::where('product_id', $product->id)
            ->whereHas('salesReturn', function ($q) use ($warehouseId) {
                if ($warehouseId) $q->where('warehouse_id', $warehouseId);
            })->with(['salesReturn.party', 'salesReturn.sale']);
        foreach ($srQuery->get() as $sri) {
            $originalSale = $sri->salesReturn->sale;
            $saleSubtotal = (float)$originalSale->total_amount + (float)$originalSale->discount_amount;
            $discountRatio = $saleSubtotal > 0 ? ((float)$originalSale->total_amount / $saleSubtotal) : 1;

            $movements->push([
                'date' => $sri->salesReturn->date,
                'created_at' => $sri->created_at,
                'type' => 'Sales Return',
                'id' => $sri->sales_return_id,
                'party' => $sri->salesReturn->party->name ?? 'Unknown',
                'qty_in' => (float)$sri->qty,
                'qty_out' => 0,
                'price' => (float)$sri->price,
                'revenue' => -round((float)$sri->total * $discountRatio, 2), // Negative Revenue
            ]);
        }

        // 5. Purchase Returns
        $prQuery = \App\Models\PurchaseReturnItem::where('product_id', $product->id)
            ->whereHas('purchaseReturn', function ($q) use ($warehouseId) {
                if ($warehouseId) $q->where('warehouse_id', $warehouseId);
            })->with(['purchaseReturn.party']);
        foreach ($prQuery->get() as $pri) {
            $movements->push([
                'date' => $pri->purchaseReturn->date,
                'created_at' => $pri->created_at,
                'type' => 'Purchase Return',
                'qty_in' => 0,
                'qty_out' => (float)$pri->qty,
                'rate' => (float)$pri->cost,
            ]);
        }

        // 6. Stock Adjustments (WAC-neutral qty movement — same treatment as calculateStockValue())
        $saQuery = \App\Models\StockAdjustmentItem::where('product_id', $product->id)
            ->whereHas('stockAdjustment', function ($q) use ($warehouseId) {
                if ($warehouseId) $q->where('warehouse_id', $warehouseId);
            })->with('stockAdjustment');
        foreach ($saQuery->get() as $sa) {
            if ($sa->type === 'addition') {
                $movements->push([
                    'date' => $sa->stockAdjustment->date,
                    'created_at' => $sa->created_at,
                    'type' => 'Stock Adjustment Add',
                    'qty_in' => (float)$sa->qty,
                    'qty_out' => 0,
                    'rate' => 0,
                ]);
            } else {
                $movements->push([
                    'date' => $sa->stockAdjustment->date,
                    'created_at' => $sa->created_at,
                    'type' => 'Stock Adjustment Sub',
                    'qty_in' => 0,
                    'qty_out' => (float)$sa->qty,
                    'rate' => 0,
                ]);
            }
        }

        // Sort for WAC calculation
        $movements = $movements->sort(function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $a['created_at'] <=> $b['created_at'];
            }
            return $a['date'] <=> $b['date'];
        });

        $runningQty = 0;
        $runningAvgCost = 0;
        $profitLossDetails = [];

        foreach ($movements as $m) {
            if ($m['type'] === 'Opening Stock' || $m['type'] === 'Purchase') {
                $prevQty = $runningQty;
                $runningQty += $m['qty_in'];
                if ($runningQty > 0) {
                    $runningAvgCost = (($prevQty * $runningAvgCost) + ($m['qty_in'] * $m['rate'])) / $runningQty;
                } else {
                    $runningAvgCost = $m['rate'];
                }
            } elseif ($m['type'] === 'Sales Return') {
                // If a customer returns, it comes back to stock.
                // We keep WAC neutral usually by bringing it back at current average cost.
                $runningQty += $m['qty_in'];

                // Profit Calculation for this return entry
                $itemCost = -($m['qty_in'] * $runningAvgCost); // Negative Cost (COGS reduced)
                $itemProfit = $m['revenue'] - $itemCost; // Negative Revenue - Negative Cost

                if ((!$startDate || $m['date'] >= $startDate) && (!$endDate || $m['date'] <= $endDate)) {
                    $profitLossDetails[] = [
                        'date' => $m['date'],
                        'type' => 'Return',
                        'sale_id' => $m['id'],
                        'party' => $m['party'],
                        'qty' => -$m['qty_in'], // Shown as negative quantity sold
                        'sale_price' => $m['price'],
                        'revenue' => $m['revenue'],
                        'cost_at_time' => round($runningAvgCost, 2),
                        'total_cost' => round($itemCost, 2),
                        'profit' => round($itemProfit, 2),
                    ];
                }
            } elseif ($m['type'] === 'Sale') {
                $itemCost = $m['qty_out'] * $runningAvgCost;
                $itemProfit = $m['revenue'] - $itemCost;

                $runningQty -= $m['qty_out'];

                if ((!$startDate || $m['date'] >= $startDate) && (!$endDate || $m['date'] <= $endDate)) {
                    $profitLossDetails[] = [
                        'date' => $m['date'],
                        'type' => 'Sale',
                        'sale_id' => $m['id'],
                        'party' => $m['party'],
                        'qty' => $m['qty_out'],
                        'sale_price' => $m['price'],
                        'revenue' => $m['revenue'],
                        'cost_at_time' => round($runningAvgCost, 2),
                        'total_cost' => round($itemCost, 2),
                        'profit' => round($itemProfit, 2),
                    ];
                }
            } elseif ($m['type'] === 'Purchase Return' || $m['type'] === 'Stock Adjustment Sub') {
                $runningQty -= $m['qty_out'];
            } elseif ($m['type'] === 'Stock Adjustment Add') {
                $runningQty += $m['qty_in'];
            }
        }

        $totalRevenue = collect($profitLossDetails)->sum('revenue');
        $totalCost = collect($profitLossDetails)->sum('total_cost');
        $totalProfit = $totalRevenue - $totalCost;

        if ($request->has('pdf')) {
            $product->load(['category', 'unit']);
            $data = compact('product', 'profitLossDetails', 'totalRevenue', 'totalCost', 'totalProfit', 'startDate', 'endDate', 'settings', 'warehouseId', 'warehouses');
            $pdf = Pdf::loadView('reports.pdf.product_profit_loss', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Product-Profit-Loss-' . $product->name . '.pdf');
        }

        return view('reports.product_profit_loss', compact('product', 'profitLossDetails', 'totalRevenue', 'totalCost', 'totalProfit', 'startDate', 'endDate', 'settings', 'warehouseId', 'warehouses'));
    }
    public function deadStock(Request $request)
    {
        $days = $request->days ?? 90;
        $thresholdDate = now()->subDays((int)$days)->toDateString();

        // Find products with stock > 0 in ANY warehouse that haven't sold since threshold date
        $products = \App\Models\Product::whereHas('stocks', function($q) {
                $q->where('qty', '>', 0);
            })
            ->whereDoesntHave('saleItems', function ($query) use ($thresholdDate) {
                $query->whereHas('sale', function ($q) use ($thresholdDate) {
                    $q->where('date', '>=', $thresholdDate);
                });
            })
            ->with(['category', 'brand', 'unit', 'stocks'])
            ->get();

        // Append info
        foreach ($products as $product) {
            $lastSaleItem = \App\Models\SaleItem::where('product_id', $product->id)
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->orderByDesc('sales.date')
                ->select('sales.date')
                ->first();

            $product->last_sale_date = $lastSaleItem ? $lastSaleItem->date : null;
            $product->days_since_last_sale = $lastSaleItem 
                ? \Carbon\Carbon::parse($lastSaleItem->date)->diffInDays(now()) 
                : 'Never Sold';
            
            // Calculate total stock and value from stocks table
            $product->current_stock = $product->stocks->sum('qty');
            $product->dead_stock_value = $product->stocks->sum(function($stock) {
                return $stock->qty * $stock->avg_cost;
            });
            
            // Calculate weighted average cost for display
            $product->avg_cost = $product->current_stock > 0 
                ? $product->dead_stock_value / $product->current_stock 
                : 0;
        }

        // Sort by days since last sale (descending)
        $products = $products->sortByDesc(function($product) {
            return is_numeric($product->days_since_last_sale) ? $product->days_since_last_sale : 99999;
        });

        $settings = Setting::first();
        $data = compact('products', 'days', 'settings');

        if ($request->has('pdf')) {
            $data['settings'] = Setting::first();
            $pdf = Pdf::loadView('reports.pdf.dead_stock', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Dead-Stock-Report.pdf');
        }

        return view('reports.dead_stock', $data);
    }

    public function stockAlert(Request $request)
    {
        $customThreshold = $request->threshold;
        
        // Base Query
        $query = \App\Models\Product::with(['category', 'brand', 'unit', 'stocks'])
            ->withSum('stocks as current_stock', 'qty');

        if ($customThreshold) {
            // Use global threshold if provided
            $query->having('current_stock', '<=', $customThreshold);
            $thresholdDisplay = $customThreshold;
        } else {
            // Use per-product alert quantity
            // Only include products where alert_quantity > 0 to avoid noise, 
            // OR include all where stock <= alert_qty (which handles 0 <= 0 for out of stock)
            $query->havingRaw('current_stock <= products.alert_quantity');
            $thresholdDisplay = 'Per Product';
        }

        $allAlertProducts = $query->orderBy('current_stock', 'asc')->get();

        // Separate Collections
        $outOfStock = $allAlertProducts->filter(function ($p) {
            return $p->current_stock <= 0;
        });

        $products = $allAlertProducts->filter(function ($p) {
            return $p->current_stock > 0;
        });

        // Calculate metrics
        foreach ($products as $product) {
            // Get last purchase
            $lastPurchase = \App\Models\PurchaseItem::where('product_id', $product->id)
                ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->orderByDesc('purchases.date')
                ->select('purchase_items.price_pkr as price', 'purchases.date')
                ->first();
            
            $product->last_purchase_price = $lastPurchase ? $lastPurchase->price : 0;
            $product->last_purchase_date = $lastPurchase ? $lastPurchase->date : null;
            
            // Reorder suggestions
            $target = $product->alert_quantity > 0 ? $product->alert_quantity : ($customThreshold ?? 0);
            $product->suggested_reorder = max($target * 2 - $product->current_stock, 10); // Simple logic: Aim for 2x alert qty
            
            // Status Label
            $effectiveThreshold = $customThreshold ?? $product->alert_quantity;
            
            if ($product->current_stock <= $effectiveThreshold / 2) {
                $product->status_label = 'Critical';
                $product->status_color = 'red';
            } else {
                $product->status_label = 'Low Stock';
                $product->status_color = 'amber';
            }
        }

        $settings = Setting::first();
        $data = [
            'products' => $products,
            'outOfStock' => $outOfStock,
            'threshold' => $thresholdDisplay,
            'customThreshold' => $customThreshold,
            'settings' => $settings
        ];

        if ($request->has('pdf')) {
            $data['settings'] = Setting::first();
            $pdf = Pdf::loadView('reports.pdf.stock_alert', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Stock-Alert-Report.pdf');
        }

        return view('reports.stock_alert', $data);
    }

    /**
     * Helper to calculate total stock value at a specific date using proper WAC (Weighted Average Cost)
     * This processes all stock movements chronologically to compute accurate running average cost.
     */
    private function calculateStockValue($date, $warehouseId = null)
    {
        $products = \App\Models\Product::all();
        $totalValue = 0;

        // 1. Bulk load all historical movements up to $date in 6 fast queries
        $osQuery = \App\Models\OpeningStock::where('date', '<', $date);
        if ($warehouseId) $osQuery->where('warehouse_id', $warehouseId);
        $osGrouped = $osQuery->get()->groupBy('product_id');

        $purQuery = \App\Models\PurchaseItem::whereHas('purchase', function($q) use ($date, $warehouseId) {
            $q->where('date', '<', $date);
            if ($warehouseId) $q->where('warehouse_id', $warehouseId);
        })->with('purchase');
        $purGrouped = $purQuery->get()->groupBy('product_id');

        $saleQuery = \App\Models\SaleItem::whereHas('sale', function($q) use ($date, $warehouseId) {
            $q->where('date', '<', $date);
            if ($warehouseId) $q->where('warehouse_id', $warehouseId);
        })->with('sale');
        $saleGrouped = $saleQuery->get()->groupBy('product_id');

        $srQuery = \App\Models\SalesReturnItem::whereHas('salesReturn', function($q) use ($date, $warehouseId) {
            $q->where('date', '<', $date);
            if ($warehouseId) $q->where('warehouse_id', $warehouseId);
        })->with('salesReturn');
        $srGrouped = $srQuery->get()->groupBy('product_id');

        $prQuery = \App\Models\PurchaseReturnItem::whereHas('purchaseReturn', function($q) use ($date, $warehouseId) {
            $q->where('date', '<', $date);
            if ($warehouseId) $q->where('warehouse_id', $warehouseId);
        })->with('purchaseReturn');
        $prGrouped = $prQuery->get()->groupBy('product_id');

        $saQuery = \App\Models\StockAdjustmentItem::whereHas('stockAdjustment', function($q) use ($date, $warehouseId) {
            $q->where('date', '<', $date);
            if ($warehouseId) $q->where('warehouse_id', $warehouseId);
        })->with('stockAdjustment');
        $saGrouped = $saQuery->get()->groupBy('product_id');

        foreach ($products as $product) {
            $movements = collect();

            // 1. Opening Stocks
            if (isset($osGrouped[$product->id])) {
                foreach ($osGrouped[$product->id] as $os) {
                    $unitRate = $os->qty > 0 ? round($os->total / $os->qty, 4) : (float)$os->rate;
                    $movements->push([
                        'date' => $os->date,
                        'created_at' => $os->created_at,
                        'type' => 'Opening Stock',
                        'qty_in' => (float)$os->qty,
                        'qty_out' => 0,
                        'rate' => $unitRate,
                    ]);
                }
            }

            // 2. Purchases (landed cost)
            if (isset($purGrouped[$product->id])) {
                foreach ($purGrouped[$product->id] as $pi) {
                    $unitRate = (float) $pi->price_pkr;
                    if ($pi->qty > 0 && $pi->cargo_amount > 0) {
                        $unitRate += $pi->cargo_amount / $pi->qty;
                    }

                    $movements->push([
                        'date' => $pi->purchase->date,
                        'created_at' => $pi->created_at,
                        'type' => 'Purchase',
                        'qty_in' => (float)$pi->qty,
                        'qty_out' => 0,
                        'rate' => $unitRate,
                    ]);
                }
            }

            // 3. Sales
            if (isset($saleGrouped[$product->id])) {
                foreach ($saleGrouped[$product->id] as $si) {
                    $movements->push([
                        'date' => $si->sale->date,
                        'created_at' => $si->created_at,
                        'type' => 'Sale',
                        'qty_in' => 0,
                        'qty_out' => (float)$si->qty,
                        'rate' => 0,
                    ]);
                }
            }

            // 4. Sales Returns (Inward)
            if (isset($srGrouped[$product->id])) {
                foreach ($srGrouped[$product->id] as $sri) {
                    $movements->push([
                        'date' => $sri->salesReturn->date,
                        'created_at' => $sri->created_at,
                        'type' => 'Sales Return',
                        'qty_in' => (float)$sri->qty,
                        'qty_out' => 0,
                        'rate' => 0,
                    ]);
                }
            }

            // 5. Purchase Returns (Outward)
            if (isset($prGrouped[$product->id])) {
                foreach ($prGrouped[$product->id] as $pri) {
                    $movements->push([
                        'date' => $pri->purchaseReturn->date,
                        'created_at' => $pri->created_at,
                        'type' => 'Purchase Return',
                        'qty_in' => 0,
                        'qty_out' => (float)$pri->qty,
                        'rate' => (float)$pri->cost,
                    ]);
                }
            }

            // 6. Stock Adjustments
            if (isset($saGrouped[$product->id])) {
                foreach ($saGrouped[$product->id] as $sa) {
                    if ($sa->type === 'addition') {
                        $movements->push([
                            'date' => $sa->stockAdjustment->date,
                            'created_at' => $sa->created_at,
                            'type' => 'Stock Adjustment Add',
                            'qty_in' => (float)$sa->qty,
                            'qty_out' => 0,
                            'rate' => 0,
                        ]);
                    } else {
                        $movements->push([
                            'date' => $sa->stockAdjustment->date,
                            'created_at' => $sa->created_at,
                            'type' => 'Stock Adjustment Sub',
                            'qty_in' => 0,
                            'qty_out' => (float)$sa->qty,
                            'rate' => 0,
                        ]);
                    }
                }
            }

            if ($movements->isEmpty()) continue;

            // Sort chronologically
            $movements = $movements->sort(function ($a, $b) {
                if ($a['date'] === $b['date']) {
                    return $a['created_at'] <=> $b['created_at'];
                }
                return $a['date'] <=> $b['date'];
            });

            // Calculate Running WAC
            $runningQty = 0;
            $runningAvgCost = 0;

            foreach ($movements as $m) {
                if ($m['type'] === 'Opening Stock' || $m['type'] === 'Purchase') {
                    $prevQty = max(0, $runningQty); // Prevent negative backorder qty from corrupting cost basis
                    $newQty = $prevQty + $m['qty_in'];
                    if ($newQty > 0) {
                        $runningAvgCost = round((($prevQty * $runningAvgCost) + ($m['qty_in'] * $m['rate'])) / $newQty, 2);
                    } else {
                        $runningAvgCost = round($m['rate'], 2);
                    }
                    $runningQty += $m['qty_in'];
                } elseif ($m['type'] === 'Sales Return' || $m['type'] === 'Stock Adjustment Add') {
                    $runningQty += $m['qty_in'];
                } elseif ($m['type'] === 'Sale' || $m['type'] === 'Purchase Return' || $m['type'] === 'Stock Adjustment Sub') {
                    $runningQty -= $m['qty_out'];
                }
            }

            $totalValue += round($runningQty * $runningAvgCost, 2);
        }

        return round($totalValue, 2);
    }

    public function productsSummary(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $search = $request->search;

        $query = \App\Models\Product::with(['unit', 'stocks']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->get()->map(function($product) use ($startDate, $endDate) {
            // calculated "In" during period
            $purchases = \App\Models\PurchaseItem::where('product_id', $product->id)
                ->whereHas('purchase', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                })->sum('qty');
            
            $salesReturns = \App\Models\SalesReturnItem::where('product_id', $product->id)
                ->whereHas('salesReturn', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                })->sum('qty');

            $openingStocks = \App\Models\OpeningStock::where('product_id', $product->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('qty');

            $product->qty_in = $purchases + $salesReturns + $openingStocks;

            // calculated "Out" during period
            $sales = \App\Models\SaleItem::where('product_id', $product->id)
                ->whereHas('sale', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                })->sum('qty');
            
            $purchaseReturns = \App\Models\PurchaseReturnItem::where('product_id', $product->id)
                ->whereHas('purchaseReturn', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                })->sum('qty');

            $product->qty_out = $sales + $purchaseReturns;

            // Current Totals (Current Status)
            $product->current_stock = $product->stocks->sum('qty');
            $totalValue = $product->stocks->sum(function($s) {
                return $s->qty * $s->avg_cost;
            });
            $product->avg_cost = $product->current_stock > 0 ? $totalValue / $product->current_stock : 0;

            return $product;
        });

        if ($request->has('pdf')) {
            $settings = Setting::first();
            $data = compact('products', 'startDate', 'endDate', 'settings');
            $pdf = Pdf::loadView('reports.pdf.products_summary', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Products-Summary-' . now()->format('d-m-Y') . '.pdf');
        }

        return view('reports.products_summary', compact('products', 'startDate', 'endDate', 'search'));
    }

    private function partyBalanceAsOf(Party $party, string $asOfDate): float
    {
        $baseOpening = $party->balance_type === 'credit' ? -$party->opening_balance : $party->opening_balance;

        $impact = LedgerEntry::where('party_id', $party->id)
            ->where('date', '<=', $asOfDate)
            ->select(DB::raw('SUM(debit) - SUM(credit) as net_impact'))
            ->first()
            ->net_impact ?? 0;

        return $baseOpening + $impact;
    }

    // Ages a party's real outstanding ledger balance against dated charge items (invoices,
    // opening balance, etc), oldest-first. If the dated items don't fully cover the balance
    // (e.g. a due amount with no linked invoice at all), the shortfall is still included so the
    // report's total always reconciles with the ledger/Account Ledger balance.
    private function buildAgingRow(Party $party, \Illuminate\Support\Collection $items, float $outstanding, string $asOfDate): ?array
    {
        if ($outstanding <= 0.01) {
            return null;
        }

        $items = $items->sortBy('date')->values();
        $shortfall = round($outstanding - round($items->sum('amount'), 2), 2);
        if ($shortfall > 0.01) {
            $fallbackDate = $items->isNotEmpty() ? $items->min('date') : (optional($party->created_at)->toDateString() ?? $asOfDate);
            $items->push(['date' => $fallbackDate, 'amount' => $shortfall]);
        }

        $paidPool = round($items->sum('amount'), 2) - $outstanding;
        $row = ['party' => $party, 'bucket_0_30' => 0, 'bucket_31_60' => 0, 'bucket_61_90' => 0, 'bucket_90_plus' => 0, 'total' => 0];

        foreach ($items as $item) {
            $due = $item['amount'] - min($paidPool, $item['amount']);
            $paidPool = max(0, $paidPool - $item['amount']);
            if ($due <= 0.01) {
                continue;
            }

            $days = \Carbon\Carbon::parse($item['date'])->diffInDays(\Carbon\Carbon::parse($asOfDate));
            if ($days <= 30) {
                $row['bucket_0_30'] += $due;
            } elseif ($days <= 60) {
                $row['bucket_31_60'] += $due;
            } elseif ($days <= 90) {
                $row['bucket_61_90'] += $due;
            } else {
                $row['bucket_90_plus'] += $due;
            }
            $row['total'] += $due;
        }

        return $row['total'] > 0.01 ? $row : null;
    }

    public function agingReport(Request $request)
    {
        $asOfDate = $request->as_of_date ?? now()->toDateString();

        // Both sides are aged from Accounts Receivable/Payable ledger entries (not Sale/Purchase
        // rows directly) plus each party's opening balance. This automatically covers cases the
        // old invoice-based approach missed: dues carried only as an opening balance (no Sale/
        // Purchase record at all), and cargo parties whose payable comes from CargoCharge entries
        // credited to their own party_id rather than from being the Purchase's vendor.
        $arAccountId = \App\Models\ChartOfAccount::where('name', 'Accounts Receivable')->value('id');
        $apAccountId = \App\Models\ChartOfAccount::where('name', 'Accounts Payable')->value('id');

        $receivableRows = [];
        $customers = Party::where('type', 'customer')->get();
        foreach ($customers as $party) {
            $items = LedgerEntry::where('party_id', $party->id)
                ->where('account_id', $arAccountId)
                ->where('debit', '>', 0)
                ->where('date', '<=', $asOfDate)
                ->get(['date', 'debit'])
                ->map(fn ($e) => ['date' => $e->date, 'amount' => (float) $e->debit]);

            $openingSigned = $party->balance_type === 'debit' ? $party->opening_balance : -$party->opening_balance;
            if ($openingSigned > 0.01) {
                $items->push(['date' => optional($party->created_at)->toDateString() ?? $asOfDate, 'amount' => round($openingSigned, 2)]);
            }

            $outstanding = round(max(0, $this->partyBalanceAsOf($party, $asOfDate)), 2);
            $row = $this->buildAgingRow($party, $items, $outstanding, $asOfDate);
            if ($row) {
                $receivableRows[$party->id] = $row;
            }
        }
        $receivables = collect($receivableRows)->sortByDesc('total')->values();

        $payableRows = [];
        $vendorsAndCargo = Party::whereIn('type', ['vendor', 'cargo'])->get();
        foreach ($vendorsAndCargo as $party) {
            $items = LedgerEntry::where('party_id', $party->id)
                ->where('account_id', $apAccountId)
                ->where('credit', '>', 0)
                ->where('date', '<=', $asOfDate)
                ->get(['date', 'credit'])
                ->map(fn ($e) => ['date' => $e->date, 'amount' => (float) $e->credit]);

            $openingSigned = $party->balance_type === 'credit' ? $party->opening_balance : -$party->opening_balance;
            if ($openingSigned > 0.01) {
                $items->push(['date' => optional($party->created_at)->toDateString() ?? $asOfDate, 'amount' => round($openingSigned, 2)]);
            }

            $outstanding = round(max(0, -$this->partyBalanceAsOf($party, $asOfDate)), 2);
            $row = $this->buildAgingRow($party, $items, $outstanding, $asOfDate);
            if ($row) {
                $payableRows[$party->id] = $row;
            }
        }
        $payables = collect($payableRows)->sortByDesc('total')->values();

        $settings = Setting::first();
        $data = compact('receivables', 'payables', 'asOfDate', 'settings');

        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('reports.pdf.aging', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Aging-Report-' . $asOfDate . '.pdf');
        }

        return view('reports.aging', $data);
    }

    public function dailyCollection(Request $request)
    {
        $startDate = $request->start_date ?? now()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $selectedAccountId = $request->account_id;

        $paymentAccounts = \App\Models\ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get();

        $query = LedgerEntry::whereIn('account_id', $paymentAccounts->pluck('id'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with('account');

        if ($selectedAccountId) {
            $query->where('account_id', $selectedAccountId);
        }

        $entries = $query->orderBy('date')->orderBy('created_at')->get();

        $accountSummary = $entries->groupBy('account_id')->map(function ($group) {
            return [
                'account' => $group->first()->account,
                'received' => $group->sum('debit'),
                'paid' => $group->sum('credit'),
            ];
        })->sortByDesc(fn($row) => $row['received'] + $row['paid'])->values();

        $totalReceived = $entries->sum('debit');
        $totalPaid = $entries->sum('credit');

        $settings = Setting::first();
        $data = compact('entries', 'accountSummary', 'totalReceived', 'totalPaid', 'paymentAccounts', 'selectedAccountId', 'startDate', 'endDate', 'settings');

        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('reports.pdf.daily_collection', $data)->setPaper('a4', 'landscape');
            return $pdf->download('Daily-Collection-Report.pdf');
        }

        return view('reports.daily_collection', $data);
    }

    public function expenseReport(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $categoryId = $request->category_id;
        $warehouseId = $request->warehouse_id;

        $query = \App\Models\Expense::with(['category', 'account', 'warehouse'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $expenses = $query->orderBy('date')->get();

        $categorySummary = $expenses->groupBy('expense_category_id')->map(function ($group) {
            return [
                'category' => $group->first()->category,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
            ];
        })->sortByDesc('total')->values();

        $totalExpense = $expenses->sum('amount');
        $categories = \App\Models\ExpenseCategory::orderBy('name')->get();
        $warehouses = \App\Models\Warehouse::all();

        $settings = Setting::first();
        $data = compact('expenses', 'categorySummary', 'totalExpense', 'categories', 'warehouses', 'categoryId', 'warehouseId', 'startDate', 'endDate', 'settings');

        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('reports.pdf.expenses', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Expense-Report.pdf');
        }

        return view('reports.expenses', $data);
    }

    public function salesAnalysis(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $limit = (int) ($request->limit ?? 10);

        // Net of Sales Returns: a customer/product with heavy returns shouldn't rank as "top"
        // on gross sales alone, so returns in the same period are subtracted before ranking.
        $returnsByParty = \App\Models\SalesReturn::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('party_id, SUM(total_amount) as total_returns')
            ->groupBy('party_id')
            ->pluck('total_returns', 'party_id');

        $topCustomers = \App\Models\Sale::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('party_id, SUM(total_amount) as total_sales, COUNT(*) as invoice_count')
            ->groupBy('party_id')
            ->with('party')
            ->get()
            ->map(function ($row) use ($returnsByParty) {
                $row->total_returns = (float) ($returnsByParty[$row->party_id] ?? 0);
                $row->net_sales = $row->total_sales - $row->total_returns;
                return $row;
            })
            ->sortByDesc('net_sales')
            ->take($limit)
            ->values();

        $returnsByProduct = \App\Models\SalesReturnItem::join('sales_returns', 'sales_return_items.sales_return_id', '=', 'sales_returns.id')
            ->whereBetween('sales_returns.date', [$startDate, $endDate])
            ->selectRaw('sales_return_items.product_id, SUM(sales_return_items.qty) as returned_qty, SUM(sales_return_items.total) as returned_amount')
            ->groupBy('sales_return_items.product_id')
            ->get()
            ->keyBy('product_id');

        $topProducts = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.date', [$startDate, $endDate])
            ->selectRaw('sale_items.product_id, SUM(sale_items.qty) as total_qty, SUM(sale_items.total) as total_revenue')
            ->groupBy('sale_items.product_id')
            ->with('product')
            ->get()
            ->map(function ($row) use ($returnsByProduct) {
                $ret = $returnsByProduct[$row->product_id] ?? null;
                $row->returned_qty = (float) ($ret->returned_qty ?? 0);
                $row->returned_amount = (float) ($ret->returned_amount ?? 0);
                $row->net_qty = $row->total_qty - $row->returned_qty;
                $row->net_revenue = $row->total_revenue - $row->returned_amount;
                return $row;
            })
            ->sortByDesc('net_revenue')
            ->take($limit)
            ->values();

        $settings = Setting::first();
        $data = compact('topCustomers', 'topProducts', 'startDate', 'endDate', 'limit', 'settings');

        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('reports.pdf.sales_analysis', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Sales-Analysis-Report.pdf');
        }

        return view('reports.sales_analysis', $data);
    }

    public function returnsSummary(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $salesReturns = \App\Models\SalesReturn::with(['party', 'sale'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $purchaseReturns = \App\Models\PurchaseReturn::with(['party', 'purchase'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $totalSalesReturns = $salesReturns->sum('total_amount');
        $totalPurchaseReturns = $purchaseReturns->sum('total_amount');

        $settings = Setting::first();
        $data = compact('salesReturns', 'purchaseReturns', 'totalSalesReturns', 'totalPurchaseReturns', 'startDate', 'endDate', 'settings');

        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('reports.pdf.returns_summary', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Returns-Summary-Report.pdf');
        }

        return view('reports.returns_summary', $data);
    }

    public function cargoChargesReport(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();
        $cargoPartyId = $request->cargo_party_id;

        $query = \App\Models\CargoCharge::with(['purchase', 'cargoParty'])
            ->whereHas('purchase', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            });

        if ($cargoPartyId) {
            $query->where('cargo_party_id', $cargoPartyId);
        }

        $cargoCharges = $query->get()->sortBy(fn($c) => $c->purchase->date ?? null)->values();

        $totalCargo = $cargoCharges->sum('total_amount');

        $partySummary = $cargoCharges->groupBy('cargo_party_id')->map(function ($group) {
            return [
                'party' => $group->first()->cargoParty,
                'total' => $group->sum('total_amount'),
                'count' => $group->count(),
            ];
        })->sortByDesc('total')->values();

        $cargoParties = \App\Models\Party::where('type', 'cargo')->orderBy('name')->get();

        $settings = Setting::first();
        $data = compact('cargoCharges', 'partySummary', 'totalCargo', 'cargoParties', 'cargoPartyId', 'startDate', 'endDate', 'settings');

        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('reports.pdf.cargo_charges', $data)->setPaper('a4', 'portrait');
            return $pdf->download('Cargo-Charges-Report.pdf');
        }

        return view('reports.cargo_charges', $data);
    }
}
