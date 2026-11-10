<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ChartOfAccount;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Expense;
use App\Models\Purchase;
use App\Traits\TracksActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfflineSyncController extends Controller
{
    use TracksActivity;

    /**
     * Fast ping endpoint for network connection check.
     */
    public function ping()
    {
        return response()->json([
            'status' => 'online',
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Download all master data needed to operate POS, Sales, and Payments offline.
     */
    public function masterData()
    {
        $customers = Party::where('status', true)->get()->map(function ($party) {
            return [
                'id' => $party->id,
                'name' => $party->name,
                'type' => $party->type,
                'phone' => $party->phone ?? '',
                'address' => $party->address ?? '',
                'price_tier' => $party->price_tier ?? 'wholesale',
                'current_balance' => (float)$party->getCurrentBalance(),
                'credit_limit' => (float)($party->credit_limit ?? 0),
                'credit_days' => (int)($party->credit_days ?? 0),
                'has_overdue' => $party->type === 'customer' ? $party->hasOverdueInvoices() : false,
            ];
        });

        $warehouses = Warehouse::where('status', true)->get(['id', 'name']);

        $products = Product::with(['stocks', 'category', 'unit', 'brand'])
            ->where('status', 1)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku ?? '',
                    'barcode' => $product->barcode ?? '',
                    'category_id' => $product->category_id,
                    'category_name' => $product->category->name ?? '',
                    'brand_id' => $product->brand_id,
                    'brand_name' => $product->brand->name ?? '',
                    'unit_name' => $product->unit->name ?? 'pc',
                    'sale_price' => (float)$product->sale_price,
                    'wholesale_price' => (float)($product->wholesale_price ?? $product->sale_price),
                    'retail_price' => (float)($product->retail_price ?? $product->sale_price),
                    'dealer_price' => (float)($product->dealer_price ?? $product->sale_price),
                    'purchase_price' => (float)$product->purchase_price,
                    'warehouse_stocks' => $product->stocks->pluck('qty', 'warehouse_id'),
                    'warehouse_costs' => $product->stocks->pluck('avg_cost', 'warehouse_id'),
                ];
            });

        $paymentAccounts = ChartOfAccount::where('type', 'Asset')
            ->whereNotIn('name', ['Inventory', 'Accounts Receivable'])
            ->where('status', true)
            ->get(['id', 'name']);

        $settings = Setting::first();

        // Cached dashboard & quick view stats for offline view
        $today = now()->toDateString();
        $dashboardStats = [
            'total_sales' => (float)Sale::sum('total_amount'),
            'total_purchases' => (float)Purchase::sum('total_amount'),
            'total_expenses' => (float)Expense::sum('amount'),
            'total_customers' => Party::where('type', 'customer')->count(),
            'total_vendors' => Party::where('type', 'vendor')->count(),
            'total_products' => Product::count(),
            'today_sales' => (float)Sale::where('date', $today)->sum('total_amount'),
            'today_sales_count' => Sale::where('date', $today)->count(),
            'cached_at' => now()->format('d M, Y h:i A'),
        ];

        return response()->json([
            'customers' => $customers,
            'products' => $products,
            'warehouses' => $warehouses,
            'payment_accounts' => $paymentAccounts,
            'settings' => [
                'walk_in_customer_id' => $settings->walk_in_customer_id ?? null,
                'shop_warehouse_id' => $settings->shop_warehouse_id ?? null,
                'currency_symbol' => $settings->currency_symbol ?? 'Rs',
                'company_name' => $settings->company_name ?? 'Wholesale Software',
            ],
            'dashboard_stats' => $dashboardStats,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Sync queued offline sales and payments to the database.
     */
    public function sync(Request $request)
    {
        $salesQueue = $request->input('sales', []);
        $paymentsQueue = $request->input('payments', []);

        $syncedSales = [];
        $syncedPayments = [];
        $errors = [];

        $settings = Setting::first();
        $accountsReceivable = ChartOfAccount::where('name', 'Accounts Receivable')->first();
        $salesAccount = ChartOfAccount::where('name', 'Sales')->first();
        $cogsAccount = ChartOfAccount::where('name', 'COGS')->first();
        $inventoryAccount = ChartOfAccount::where('name', 'Inventory')->first();

        // 1. Process Sales Queue
        foreach ($salesQueue as $rawSale) {
            $offlineId = $rawSale['offline_id'] ?? null;
            if (!$offlineId) {
                continue;
            }

            // Idempotency check: Already synced earlier?
            $existing = Sale::where('offline_id', $offlineId)->first();
            if ($existing) {
                $syncedSales[] = [
                    'offline_id' => $offlineId,
                    'server_id' => $existing->id,
                    'already_synced' => true,
                ];
                continue;
            }

            try {
                $sale = DB::transaction(function () use ($rawSale, $offlineId, $settings, $accountsReceivable, $salesAccount, $cogsAccount, $inventoryAccount) {
                    $partyId = $rawSale['party_id'];
                    $warehouseId = $rawSale['warehouse_id'];
                    $saleDate = $rawSale['date'] ?? now()->toDateString();
                    $note = ($rawSale['note'] ?? '') . ' [Offline Synced]';
                    $payments = $rawSale['payments'] ?? [];
                    $items = $rawSale['items'] ?? [];

                    $subtotal = 0;
                    foreach ($items as $item) {
                        $subtotal += round(($item['qty'] ?? 0) * ($item['price'] ?? 0), 2);
                    }

                    $discount = (float)($rawSale['discount_amount'] ?? 0);
                    $total = max(0, $subtotal - $discount);

                    $party = Party::findOrFail($partyId);
                    $currentBalance = $party->getCurrentBalance();
                    $totalPaid = collect($payments)->sum(fn($p) => (float)($p['amount'] ?? 0));

                    $advanceUsed = 0;
                    if ($currentBalance < 0) {
                        $advanceAvailable = abs($currentBalance);
                        $advanceUsed = min($advanceAvailable, $total);
                    }
                    $invoicePaidAmount = min($total, $totalPaid + $advanceUsed);

                    $sale = Sale::create([
                        'offline_id' => $offlineId,
                        'party_id' => $partyId,
                        'warehouse_id' => $warehouseId,
                        'account_id' => $payments[0]['account_id'] ?? null,
                        'date' => $saleDate,
                        'total_amount' => $total,
                        'discount_amount' => $discount,
                        'paid_amount' => $invoicePaidAmount,
                        'note' => $note,
                    ]);

                    $totalCogs = 0;
                    foreach ($items as $item) {
                        $itemTotal = round($item['qty'] * $item['price'], 2);
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $item['product_id'],
                            'qty' => $item['qty'],
                            'price' => $item['price'],
                            'total' => $itemTotal,
                        ]);

                        $stock = Stock::where('product_id', $item['product_id'])
                            ->where('warehouse_id', $warehouseId)
                            ->lockForUpdate()
                            ->first();

                        if ($stock) {
                            $totalCogs += round($item['qty'] * $stock->avg_cost, 2);
                            $stock->qty -= $item['qty'];
                            $stock->save();
                        } else {
                            Stock::create([
                                'product_id' => $item['product_id'],
                                'warehouse_id' => $warehouseId,
                                'qty' => -$item['qty'],
                                'avg_cost' => 0,
                            ]);
                        }
                    }

                    // Accounting entries
                    if ($accountsReceivable && $salesAccount) {
                        LedgerEntry::create([
                            'account_id' => $accountsReceivable->id,
                            'party_id' => $sale->party_id,
                            'warehouse_id' => $sale->warehouse_id,
                            'debit' => $sale->total_amount,
                            'credit' => 0,
                            'date' => $sale->date,
                            'reference_type' => 'sale',
                            'reference_id' => $sale->id,
                            'note' => "Offline Synced Sale to {$party->name}",
                        ]);

                        LedgerEntry::create([
                            'account_id' => $salesAccount->id,
                            'party_id' => null,
                            'warehouse_id' => $sale->warehouse_id,
                            'debit' => 0,
                            'credit' => $sale->total_amount,
                            'date' => $sale->date,
                            'reference_type' => 'sale',
                            'reference_id' => $sale->id,
                            'note' => "Offline Synced Sale revenue",
                        ]);
                    }

                    if ($totalCogs > 0 && $cogsAccount && $inventoryAccount) {
                        LedgerEntry::create([
                            'account_id' => $cogsAccount->id,
                            'party_id' => null,
                            'warehouse_id' => $sale->warehouse_id,
                            'debit' => $totalCogs,
                            'credit' => 0,
                            'date' => $sale->date,
                            'reference_type' => 'sale_cogs',
                            'reference_id' => $sale->id,
                            'note' => "COGS for Offline Sale #{$sale->id}",
                        ]);

                        LedgerEntry::create([
                            'account_id' => $inventoryAccount->id,
                            'party_id' => null,
                            'warehouse_id' => $sale->warehouse_id,
                            'debit' => 0,
                            'credit' => $totalCogs,
                            'date' => $sale->date,
                            'reference_type' => 'sale_cogs',
                            'reference_id' => $sale->id,
                            'note' => "Inventory reduced for Offline Sale #{$sale->id}",
                        ]);
                    }

                    // Payments
                    foreach ($payments as $paymentData) {
                        $pAmount = (float)($paymentData['amount'] ?? 0);
                        if ($pAmount <= 0 || empty($paymentData['account_id'])) {
                            continue;
                        }

                        $pAccount = ChartOfAccount::find($paymentData['account_id']);
                        if (!$pAccount) continue;

                        LedgerEntry::create([
                            'account_id' => $pAccount->id,
                            'party_id' => null,
                            'warehouse_id' => $sale->warehouse_id,
                            'debit' => $pAmount,
                            'credit' => 0,
                            'date' => $sale->date,
                            'reference_type' => 'payment_received',
                            'reference_id' => $sale->id,
                            'note' => "Payment for Offline Sale #{$sale->id}",
                        ]);

                        if ($accountsReceivable) {
                            LedgerEntry::create([
                                'account_id' => $accountsReceivable->id,
                                'party_id' => $sale->party_id,
                                'warehouse_id' => $sale->warehouse_id,
                                'debit' => 0,
                                'credit' => $pAmount,
                                'date' => $sale->date,
                                'reference_type' => 'payment_received',
                                'reference_id' => $sale->id,
                                'note' => "Payment received from {$party->name} for Offline Sale #{$sale->id}",
                            ]);
                        }
                    }

                    $this->logActivity('Synced Offline Sale', 'Sales', $sale->id, null, $sale->toArray());

                    return $sale;
                });

                $syncedSales[] = [
                    'offline_id' => $offlineId,
                    'server_id' => $sale->id,
                    'total_amount' => $sale->total_amount,
                ];
            } catch (\Throwable $e) {
                Log::error("Offline sale sync error [{$offlineId}]: " . $e->getMessage());
                $errors[] = [
                    'type' => 'sale',
                    'offline_id' => $offlineId,
                    'message' => $e->getMessage(),
                ];
            }
        }

        // 2. Process Payments Queue
        foreach ($paymentsQueue as $rawPayment) {
            $offlineId = $rawPayment['offline_id'] ?? null;
            if (!$offlineId) {
                continue;
            }

            $existing = Payment::where('offline_id', $offlineId)->first();
            if ($existing) {
                $syncedPayments[] = [
                    'offline_id' => $offlineId,
                    'server_id' => $existing->id,
                    'already_synced' => true,
                ];
                continue;
            }

            try {
                $payment = DB::transaction(function () use ($rawPayment, $offlineId) {
                    $party = Party::findOrFail($rawPayment['party_id']);
                    $warehouseId = $rawPayment['warehouse_id'] ?? Warehouse::first()->id ?? 1;
                    $fromAccount = ChartOfAccount::findOrFail($rawPayment['from_account_id']);
                    $amount = (float)$rawPayment['amount'];
                    $paymentType = $rawPayment['payment_type'] ?? 'received';
                    $date = $rawPayment['date'] ?? now()->toDateString();
                    $note = ($rawPayment['note'] ?? '') . ' [Offline Synced]';

                    $payment = Payment::create([
                        'offline_id' => $offlineId,
                        'party_id' => $party->id,
                        'warehouse_id' => $warehouseId,
                        'from_account_id' => $fromAccount->id,
                        'amount' => $amount,
                        'payment_type' => $paymentType,
                        'date' => $date,
                        'note' => $note,
                    ]);

                    $receivableAcc = ChartOfAccount::where('name', 'Accounts Receivable')->first();
                    $payableAcc = ChartOfAccount::where('name', 'Accounts Payable')->first();

                    $targetAcc = ($party->type === 'customer') ? $receivableAcc : $payableAcc;

                    if ($paymentType === 'received') {
                        // Debit cash/bank account
                        LedgerEntry::create([
                            'account_id' => $fromAccount->id,
                            'party_id' => null,
                            'warehouse_id' => $warehouseId,
                            'debit' => $amount,
                            'credit' => 0,
                            'date' => $date,
                            'reference_type' => 'payment',
                            'reference_id' => $payment->id,
                            'note' => "Offline Payment received from {$party->name}",
                        ]);

                        // Credit party AR/AP
                        if ($targetAcc) {
                            LedgerEntry::create([
                                'account_id' => $targetAcc->id,
                                'party_id' => $party->id,
                                'warehouse_id' => $warehouseId,
                                'debit' => 0,
                                'credit' => $amount,
                                'date' => $date,
                                'reference_type' => 'payment',
                                'reference_id' => $payment->id,
                                'note' => "Offline Payment from {$party->name}",
                            ]);
                        }
                    } else {
                        // Paid
                        if ($targetAcc) {
                            LedgerEntry::create([
                                'account_id' => $targetAcc->id,
                                'party_id' => $party->id,
                                'warehouse_id' => $warehouseId,
                                'debit' => $amount,
                                'credit' => 0,
                                'date' => $date,
                                'reference_type' => 'payment',
                                'reference_id' => $payment->id,
                                'note' => "Offline Payment to {$party->name}",
                            ]);
                        }

                        LedgerEntry::create([
                            'account_id' => $fromAccount->id,
                            'party_id' => null,
                            'warehouse_id' => $warehouseId,
                            'debit' => 0,
                            'credit' => $amount,
                            'date' => $date,
                            'reference_type' => 'payment',
                            'reference_id' => $payment->id,
                            'note' => "Offline Payment paid to {$party->name}",
                        ]);
                    }

                    $this->logActivity('Synced Offline Payment', 'Payments', $payment->id, null, $payment->toArray());

                    return $payment;
                });

                $syncedPayments[] = [
                    'offline_id' => $offlineId,
                    'server_id' => $payment->id,
                    'amount' => $payment->amount,
                ];
            } catch (\Throwable $e) {
                Log::error("Offline payment sync error [{$offlineId}]: " . $e->getMessage());
                $errors[] = [
                    'type' => 'payment',
                    'offline_id' => $offlineId,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => count($errors) === 0,
            'synced_sales' => $syncedSales,
            'synced_payments' => $syncedPayments,
            'errors' => $errors,
            'message' => 'Offline sync completed. ' . count($syncedSales) . ' sales and ' . count($syncedPayments) . ' payments synchronized.',
        ]);
    }
}
