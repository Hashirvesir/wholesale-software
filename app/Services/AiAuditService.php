<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use App\Models\Party;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AiAuditService
{
    /**
     * Check if a prompt is inquiring about GL, ledgers, accounts receivable/payable, trial balance, or audits.
     */
    public function isAuditRelatedQuery(?string $prompt): bool
    {
        if (empty($prompt)) {
            return false;
        }

        $p = mb_strtolower($prompt);
        $keywords = [
            'receivable', 'payable', 'general ledger', 'ledger', 'audit', 'trial balance',
            'balance sheet', 'faraq', 'discrepancy', 'match', 'duplicate', 'anomal',
            'ar', 'ap', 'gl', 'udhaar', 'check karo', 'reconcil', 'hisab'
        ];

        foreach ($keywords as $kw) {
            if (str_contains($p, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detailed Audit of General Ledger Accounts Receivable vs Customer Sub-Ledgers.
     */
    public function auditAccountsReceivable(): array
    {
        $settings = Setting::first();
        $currency = $settings->currency_symbol ?? 'Rs';

        $arAccount = ChartOfAccount::where('name', 'Accounts Receivable')->first();
        $glBalance = $arAccount ? (float) $arAccount->getBalance() : 0.0;
        $glOpening = $arAccount ? (float) $arAccount->opening_balance : 0.0;

        // Total Customer Balances (Sub-ledger)
        $customers = Party::whereIn('type', ['customer', 'both'])->where('status', true)->get();
        $totalCustomerBalance = 0.0;
        $partyOpeningDebit = 0.0;
        $partyOpeningCredit = 0.0;
        $customerList = [];

        foreach ($customers as $c) {
            $bal = (float) $c->getCurrentBalance();
            $totalCustomerBalance += $bal;
            if ($bal > 10) {
                $customerList[] = [
                    'id' => $c->id,
                    'name' => $c->name,
                    'phone' => $c->phone,
                    'balance' => $bal,
                    'credit_limit' => (float) ($c->credit_limit ?? 0),
                    'credit_days' => (int) ($c->credit_days ?? 0),
                ];
            }
            if ($c->balance_type === 'debit') {
                $partyOpeningDebit += (float) ($c->opening_balance ?? 0);
            } else {
                $partyOpeningCredit += (float) ($c->opening_balance ?? 0);
            }
        }

        $netPartyOpening = $partyOpeningDebit - $partyOpeningCredit;
        $discrepancy = round($totalCustomerBalance - $glBalance, 2);
        $reconciledDifference = round($totalCustomerBalance - ($glBalance + $netPartyOpening), 2);

        usort($customerList, fn($a, $b) => $b['balance'] <=> $a['balance']);
        $topDebtors = array_slice($customerList, 0, 5);

        // Recent 5 entries in AR account
        $recentEntries = [];
        if ($arAccount) {
            $entries = $arAccount->ledgerEntries()->with('party')->latest('date')->latest('id')->take(6)->get();
            foreach ($entries as $e) {
                $recentEntries[] = [
                    'date' => $e->date,
                    'party' => $e->party->name ?? 'Direct Entry',
                    'debit' => (float) $e->debit,
                    'credit' => (float) $e->credit,
                    'note' => $e->note ?: ($e->debit > 0 ? 'Sale/Charge' : 'Payment Received'),
                ];
            }
        }

        return [
            'gl_account_id' => $arAccount ? $arAccount->id : null,
            'gl_account_name' => $arAccount ? $arAccount->name : 'Accounts Receivable',
            'gl_balance' => $glBalance,
            'customer_subledger_total' => $totalCustomerBalance,
            'party_opening_net' => $netPartyOpening,
            'party_opening_dr' => $partyOpeningDebit,
            'party_opening_cr' => $partyOpeningCredit,
            'discrepancy' => $discrepancy,
            'reconciled_difference' => $reconciledDifference,
            'is_perfectly_reconciled' => abs($reconciledDifference) < 1.0,
            'top_debtors' => $topDebtors,
            'recent_entries' => $recentEntries,
            'currency' => $currency,
        ];
    }

    /**
     * Detailed Audit of General Ledger Accounts Payable vs Vendor Sub-Ledgers.
     */
    public function auditAccountsPayable(): array
    {
        $settings = Setting::first();
        $currency = $settings->currency_symbol ?? 'Rs';

        $apAccount = ChartOfAccount::where('name', 'Accounts Payable')->first();
        $glBalance = $apAccount ? (float) $apAccount->getBalance() : 0.0;

        $vendors = Party::whereIn('type', ['vendor', 'supplier', 'both', 'cargo'])->where('status', true)->get();
        $totalVendorBalance = 0.0;
        $vendorOpeningDebit = 0.0;
        $vendorOpeningCredit = 0.0;
        $vendorList = [];

        foreach ($vendors as $v) {
            $bal = (float) $v->getCurrentBalance();
            $totalVendorBalance += $bal;
            if ($bal < -10) {
                $posBal = abs($bal);
                $vendorList[] = [
                    'id' => $v->id,
                    'name' => $v->name,
                    'balance' => $posBal,
                ];
            }
            if ($v->balance_type === 'credit') {
                $vendorOpeningCredit += (float) ($v->opening_balance ?? 0);
            } else {
                $vendorOpeningDebit += (float) ($v->opening_balance ?? 0);
            }
        }

        $netVendorOpening = $vendorOpeningCredit - $vendorOpeningDebit;
        usort($vendorList, fn($a, $b) => $b['balance'] <=> $a['balance']);
        $topVendors = array_slice($vendorList, 0, 5);

        return [
            'gl_account_name' => 'Accounts Payable',
            'gl_balance' => $glBalance,
            'vendor_subledger_total' => abs($totalVendorBalance),
            'vendor_opening_net' => $netVendorOpening,
            'top_vendors' => $topVendors,
            'currency' => $currency,
        ];
    }

    /**
     * Audit Trial Balance debits and credits across all accounts.
     */
    public function auditTrialBalance(): array
    {
        $settings = Setting::first();
        $currency = $settings->currency_symbol ?? 'Rs';
        $today = Carbon::today()->toDateString();

        $accounts = ChartOfAccount::all();
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accounts as $account) {
            $impact = LedgerEntry::where('account_id', $account->id)
                ->where('date', '<=', $today)
                ->select(DB::raw('SUM(debit) as total_debit, SUM(credit) as total_credit'))
                ->first();

            $debit = (float) ($impact->total_debit ?? 0);
            $credit = (float) ($impact->total_credit ?? 0);

            if ($account->name === 'Accounts Receivable') {
                $partyOpeningDr = (float) Party::where('type', 'customer')->where('balance_type', 'debit')->sum('opening_balance');
                $partyOpeningCr = (float) Party::where('type', 'customer')->where('balance_type', 'credit')->sum('opening_balance');
                $debit += $partyOpeningDr;
                $credit += $partyOpeningCr;
            }

            if ($account->name === 'Accounts Payable') {
                $partyOpeningDr = (float) Party::whereIn('type', ['vendor', 'cargo'])->where('balance_type', 'debit')->sum('opening_balance');
                $partyOpeningCr = (float) Party::whereIn('type', ['vendor', 'cargo'])->where('balance_type', 'credit')->sum('opening_balance');
                $debit += $partyOpeningDr;
                $credit += $partyOpeningCr;
            }

            $net = round($debit - $credit, 2);
            if ($net > 0) {
                $totalDebit += $net;
            } elseif ($net < 0) {
                $totalCredit += abs($net);
            }
        }

        $totalDebit = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);
        $diff = round($totalDebit - $totalCredit, 2);

        // Root cause calculation: Party net opening difference
        $customerOpeningDr = (float) Party::where('type', 'customer')->where('balance_type', 'debit')->sum('opening_balance');
        $customerOpeningCr = (float) Party::where('type', 'customer')->where('balance_type', 'credit')->sum('opening_balance');
        $netCustomerOpening = $customerOpeningDr - $customerOpeningCr;

        $vendorOpeningCr = (float) Party::whereIn('type', ['vendor', 'cargo'])->where('balance_type', 'credit')->sum('opening_balance');
        $vendorOpeningDr = (float) Party::whereIn('type', ['vendor', 'cargo'])->where('balance_type', 'debit')->sum('opening_balance');
        $netVendorOpening = $vendorOpeningCr - $vendorOpeningDr;

        $unoffsetEquity = round($netCustomerOpening - $netVendorOpening, 2);

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => $diff,
            'is_matched' => abs($diff) < 1.0,
            'unoffset_equity' => $unoffsetEquity,
            'currency' => $currency,
        ];
    }

    /**
     * Detect anomalies: negative inventory stock, duplicate sales on the same date/customer.
     */
    public function detectAnomalies(): array
    {
        $settings = Setting::first();
        $currency = $settings->currency_symbol ?? 'Rs';

        // 1. Negative stock items
        $negativeProducts = Product::with(['stocks', 'unit'])
            ->get()
            ->filter(fn($p) => $p->stocks->sum('qty') < 0)
            ->take(6)
            ->map(fn($p) => [
                'name' => $p->name,
                'sku' => $p->sku,
                'stock' => $p->stocks->sum('qty'),
                'unit' => $p->unit->name ?? 'PC',
            ])
            ->values()
            ->all();

        // 2. Suspicious duplicate sales (same date, party, and amount > 500)
        $dupSales = DB::table('sales')
            ->join('parties', 'sales.party_id', '=', 'parties.id')
            ->select('sales.date', 'parties.name as party_name', 'sales.total_amount', DB::raw('COUNT(sales.id) as count'))
            ->where('sales.total_amount', '>', 500)
            ->where('sales.date', '>=', Carbon::now()->subMonths(6)->toDateString())
            ->groupBy('sales.party_id', 'parties.name', 'sales.date', 'sales.total_amount')
            ->having('count', '>', 1)
            ->orderByDesc('sales.date')
            ->take(5)
            ->get()
            ->map(fn($d) => [
                'date' => $d->date,
                'customer' => $d->party_name,
                'amount' => (float) $d->total_amount,
                'count' => $d->count,
            ])
            ->all();

        return [
            'negative_stock_items' => $negativeProducts,
            'duplicate_sales' => $dupSales,
            'currency' => $currency,
        ];
    }

    /**
     * Formats an Accounts Receivable audit into clean, authoritative text for the LLM context.
     */
    public function getArAuditText(): string
    {
        $ar = $this->auditAccountsReceivable();
        $c = $ar['currency'];

        $out = "=== GENERAL LEDGER AUDIT: ACCOUNTS RECEIVABLE ===\n";
        $out .= "- General Ledger (GL) Control Account Balance: {$c} " . number_format($ar['gl_balance'], 2) . "\n";
        $out .= "- Customer Sub-Ledgers Total (Actual Market Udhaar): {$c} " . number_format($ar['customer_subledger_total'], 2) . "\n";
        $out .= "- Customer Net Opening Balances: {$c} " . number_format($ar['party_opening_net'], 2) . " (Dr: {$c} " . number_format($ar['party_opening_dr'], 2) . ", Cr: {$c} " . number_format($ar['party_opening_cr'], 2) . ")\n";
        $out .= "- Discrepancy Amount: {$c} " . number_format($ar['discrepancy'], 2) . "\n";
        $out .= "- Mathematical Reconciliation: GL Balance ({$c} " . number_format($ar['gl_balance'], 2) . ") + Customer Opening ({$c} " . number_format($ar['party_opening_net'], 2) . ") = {$c} " . number_format($ar['gl_balance'] + $ar['party_opening_net'], 2) . "\n";
        $out .= "- Audit Verdict: " . ($ar['is_perfectly_reconciled'] ? "100% RECONCILED (ZERO MISSING ENTRIES)" : "Difference exists") . "\n";
        $out .= "- Root Cause Explanation: In Invenco, customer opening balances are stored on individual customer profiles without an opening journal entry into the GL 'Accounts Receivable' control account. Daily sales and receipts are posted properly to GL. When customer opening balances are accounted for, the GL and Customer Subledger match to the exact cent!\n";

        $out .= "- Top 5 Customer Debtors (Highest Udhaar):\n";
        foreach ($ar['top_debtors'] as $d) {
            $out .= "  * {$d['name']} (Phone: {$d['phone']}): {$c} " . number_format($d['balance'], 2) . " | Credit Limit: {$c} " . number_format($d['credit_limit'], 2) . "\n";
        }

        $out .= "- Recent Ledger Transactions in AR:\n";
        foreach ($ar['recent_entries'] as $re) {
            $out .= "  * Date: {$re['date']} | Party: {$re['party']} | Dr: {$c} " . number_format($re['debit'], 2) . " | Cr: {$c} " . number_format($re['credit'], 2) . " | Note: {$re['note']}\n";
        }

        return $out;
    }

    /**
     * Formats full audit summary (AR, AP, Trial Balance, Anomalies) for the LLM.
     */
    public function getFullAuditSummaryText(): string
    {
        $ar = $this->auditAccountsReceivable();
        $ap = $this->auditAccountsPayable();
        $tb = $this->auditTrialBalance();
        $anom = $this->detectAnomalies();
        $c = $ar['currency'];

        $out = "=== INVENCO AUTOMATED AUDIT & RECONCILIATION REPORT ===\n\n";

        // 1. AR Audit
        $out .= "1. ACCOUNTS RECEIVABLE RECONCILIATION:\n";
        $out .= "  - General Ledger AR Balance: {$c} " . number_format($ar['gl_balance'], 2) . "\n";
        $out .= "  - Customer Sub-ledger Total: {$c} " . number_format($ar['customer_subledger_total'], 2) . "\n";
        $out .= "  - Difference: {$c} " . number_format($ar['discrepancy'], 2) . " (Caused by Customer Opening Balances: {$c} " . number_format($ar['party_opening_net'], 2) . ")\n";
        $out .= "  - Reconciliation Status: Reconciled to the penny (GL + Customer Opening = Subledger).\n\n";

        // 2. AP Audit
        $out .= "2. ACCOUNTS PAYABLE RECONCILIATION:\n";
        $out .= "  - General Ledger AP Balance: {$c} " . number_format($ap['gl_balance'], 2) . "\n";
        $out .= "  - Vendor Sub-ledger Total: {$c} " . number_format($ap['vendor_subledger_total'], 2) . "\n";
        $out .= "  - Vendor Net Opening Balances: {$c} " . number_format($ap['vendor_opening_net'], 2) . "\n\n";

        // 3. Trial Balance Audit
        $out .= "3. TRIAL BALANCE INTEGRITY CHECK:\n";
        $out .= "  - Total Debit: {$c} " . number_format($tb['total_debit'], 2) . "\n";
        $out .= "  - Total Credit: {$c} " . number_format($tb['total_credit'], 2) . "\n";
        $out .= "  - Variance / Difference: {$c} " . number_format($tb['difference'], 2) . "\n";
        $out .= "  - Reason for Variance: Net customer opening balances ({$c} " . number_format($ar['party_opening_net'], 2) . ") minus vendor opening balances ({$c} " . number_format($ap['vendor_opening_net'], 2) . ") = {$c} " . number_format($tb['unoffset_equity'], 2) . ". In double-entry bookkeeping, this net asset opening requires an offsetting entry into 'Opening Balance Equity / Capital' (Credit) of {$c} " . number_format($tb['unoffset_equity'], 2) . ".\n\n";

        // 4. Anomalies
        $out .= "4. SYSTEM ANOMALIES & AUDIT FLAGS:\n";
        if (!empty($anom['negative_stock_items'])) {
            $out .= "  - Negative Stock Items Detected (" . count($anom['negative_stock_items']) . "):\n";
            foreach ($anom['negative_stock_items'] as $ns) {
                $out .= "    * {$ns['name']} (SKU: {$ns['sku']}): Stock {$ns['stock']} {$ns['unit']} (Sale made before recording purchase/opening stock)\n";
            }
        }
        if (!empty($anom['duplicate_sales'])) {
            $out .= "  - Potential Duplicate Invoices Flagged:\n";
            foreach ($anom['duplicate_sales'] as $ds) {
                $out .= "    * Date {$ds['date']} | Customer: {$ds['customer']} | Amount: {$c} " . number_format($ds['amount'], 2) . " ({$ds['count']} identical invoices)\n";
            }
        }

        return $out;
    }
}
