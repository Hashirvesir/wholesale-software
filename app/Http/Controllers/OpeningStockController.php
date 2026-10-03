<?php

namespace App\Http\Controllers;

use App\Models\OpeningStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\TracksActivity;

class OpeningStockController extends Controller
{
    use TracksActivity;
    public function index()
    {
        $openingStocks = OpeningStock::with(['product', 'warehouse'])->latest()->paginate(10);
        return view('opening_stocks.index', compact('openingStocks'));
    }

    public function create()
    {
        $products = Product::where('status', true)->get();
        $warehouses = Warehouse::where('status', true)->get();
        return view('opening_stocks.create', compact('products', 'warehouses'));
    }

    public function edit(OpeningStock $openingStock)
    {
        $products = Product::where('status', true)->get();
        $warehouses = Warehouse::where('status', true)->get();
        return view('opening_stocks.edit', compact('openingStock', 'products', 'warehouses'));
    }

    public function update(Request $request, OpeningStock $openingStock)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'qty' => 'required|numeric|min:0.01',
            'rate' => 'required|numeric|min:0',
            'cargo_amount' => 'nullable|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $oldPayload = $openingStock->toArray();

        DB::transaction(function () use ($request, $openingStock) {
            // Reverse the old stock effect on the old product/warehouse
            $oldStock = Stock::where('product_id', $openingStock->product_id)
                          ->where('warehouse_id', $openingStock->warehouse_id)
                          ->first();

            if ($oldStock) {
                $newQty = $oldStock->qty - $openingStock->qty;
                if ($newQty <= 0) {
                    $oldStock->update(['qty' => 0, 'avg_cost' => 0]);
                } else {
                    $newAvgCost = (($oldStock->qty * $oldStock->avg_cost) - $openingStock->total) / $newQty;
                    $oldStock->update([
                        'qty' => $newQty,
                        'avg_cost' => max(0, round($newAvgCost, 2))
                    ]);
                }
            }

            $cargoAmount = round($request->cargo_amount ?? 0, 2);
            $total = round($request->qty * $request->rate + $cargoAmount, 2);

            // Apply the new stock effect on the new product/warehouse
            $stock = Stock::where('product_id', $request->product_id)
                          ->where('warehouse_id', $request->warehouse_id)
                          ->first();

            if ($stock) {
                $currentQty = $stock->qty;
                $currentAvgCost = $stock->avg_cost;
                $addedQty = $request->qty;
                $addedRate = $request->rate;

                $newQty = $currentQty + $addedQty;
                $newAvgCost = (($currentQty * $currentAvgCost) + ($addedQty * $addedRate) + $cargoAmount) / $newQty;

                $stock->update([
                    'qty' => $newQty,
                    'avg_cost' => round($newAvgCost, 2)
                ]);
            } else {
                Stock::create([
                    'product_id' => $request->product_id,
                    'warehouse_id' => $request->warehouse_id,
                    'qty' => $request->qty,
                    'avg_cost' => round(($request->qty * $request->rate + $cargoAmount) / $request->qty, 2)
                ]);
            }

            $openingStock->update([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'qty' => $request->qty,
                'rate' => $request->rate,
                'cargo_amount' => $cargoAmount,
                'total' => $total,
                'date' => $request->date,
                'note' => $request->note,
            ]);

            // Re-create ledger entries
            \App\Models\LedgerEntry::where('reference_type', 'opening_stock')->where('reference_id', $openingStock->id)->delete();

            $inventoryAcc = \App\Models\ChartOfAccount::where('name', 'Inventory')->first();
            $equityAcc = \App\Models\ChartOfAccount::firstOrCreate(
                ['name' => 'Opening Balance Equity'],
                ['type' => 'Equity', 'status' => true]
            );

            \App\Models\LedgerEntry::create([
                'account_id' => $inventoryAcc->id,
                'party_id' => null,
                'warehouse_id' => $request->warehouse_id,
                'debit' => $total,
                'credit' => 0,
                'date' => $request->date,
                'reference_type' => 'opening_stock',
                'reference_id' => $openingStock->id,
                'note' => "Opening Stock for {$openingStock->product->name}",
            ]);

            \App\Models\LedgerEntry::create([
                'account_id' => $equityAcc->id,
                'party_id' => null,
                'warehouse_id' => $request->warehouse_id,
                'debit' => 0,
                'credit' => $total,
                'date' => $request->date,
                'reference_type' => 'opening_stock',
                'reference_id' => $openingStock->id,
                'note' => "Opening Stock for {$openingStock->product->name}",
            ]);
        });

        $this->logActivity('Updated Opening Stock', 'Opening Stocks', $openingStock->id, $oldPayload, $openingStock->fresh()->toArray());

        return redirect()->route('opening-stocks.index')->with('success', 'Opening stock updated successfully and stock adjusted.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'qty' => 'required|numeric|min:0.01',
            'rate' => 'required|numeric|min:0',
            'cargo_amount' => 'nullable|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $openingStock = DB::transaction(function () use ($request) {
            $cargoAmount = round($request->cargo_amount ?? 0, 2);
            $total = round($request->qty * $request->rate + $cargoAmount, 2);

            // Create Opening Stock entry
            $openingStock = OpeningStock::create([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'qty' => $request->qty,
                'rate' => $request->rate,
                'cargo_amount' => $cargoAmount,
                'total' => $total,
                'date' => $request->date,
                'note' => $request->note,
            ]);

            // Update Stock table
            $stock = Stock::where('product_id', $request->product_id)
                          ->where('warehouse_id', $request->warehouse_id)
                          ->first();

            if ($stock) {
                $currentQty = $stock->qty;
                $currentAvgCost = $stock->avg_cost;
                $addedQty = $request->qty;
                $addedRate = $request->rate;

                $newQty = $currentQty + $addedQty;
                $newAvgCost = (($currentQty * $currentAvgCost) + ($addedQty * $addedRate) + $cargoAmount) / $newQty;

                $stock->update([
                    'qty' => $newQty,
                    'avg_cost' => round($newAvgCost, 2)
                ]);
            } else {
                Stock::create([
                    'product_id' => $request->product_id,
                    'warehouse_id' => $request->warehouse_id,
                    'qty' => $request->qty,
                    'avg_cost' => round(($request->qty * $request->rate + $cargoAmount) / $request->qty, 2)
                ]);
            }

            // --- LEDGER ENTRIES ---
            $inventoryAcc = \App\Models\ChartOfAccount::where('name', 'Inventory')->first();
            $equityAcc = \App\Models\ChartOfAccount::firstOrCreate(
                ['name' => 'Opening Balance Equity'],
                ['type' => 'Equity', 'status' => true]
            );

            // Debit Inventory
            \App\Models\LedgerEntry::create([
                'account_id' => $inventoryAcc->id,
                'party_id' => null,
                'warehouse_id' => $request->warehouse_id,
                'debit' => $total,
                'credit' => 0,
                'date' => $request->date,
                'reference_type' => 'opening_stock',
                'reference_id' => $openingStock->id,
                'note' => "Opening Stock for {$openingStock->product->name}",
            ]);

            // Credit Opening Balance Equity
            \App\Models\LedgerEntry::create([
                'account_id' => $equityAcc->id,
                'party_id' => null,
                'warehouse_id' => $request->warehouse_id,
                'debit' => 0,
                'credit' => $total,
                'date' => $request->date,
                'reference_type' => 'opening_stock',
                'reference_id' => $openingStock->id,
                'note' => "Opening Stock for {$openingStock->product->name}",
            ]);
            return $openingStock;
        });

        $this->logActivity('Created Opening Stock', 'Opening Stocks', $openingStock->id, null, $openingStock->toArray());

        return redirect()->route('opening-stocks.index')->with('success', 'Opening stock added successfully and stock updated.');
    }

    public function destroy(OpeningStock $openingStock)
    {
        // For simplicity, we might not allow deleting opening stock if it affects historical average cost
        // But if we do, we need to reverse the stock calculation. 
        // For now, let's just implement deletion with a warning.
        
        DB::transaction(function () use ($openingStock) {
            $stock = Stock::where('product_id', $openingStock->product_id)
                          ->where('warehouse_id', $openingStock->warehouse_id)
                          ->first();

            if ($stock) {
                $newQty = $stock->qty - $openingStock->qty;
                if ($newQty <= 0) {
                    $stock->update(['qty' => 0, 'avg_cost' => 0]);
                } else {
                    $newAvgCost = (($stock->qty * $stock->avg_cost) - $openingStock->total) / $newQty;
                    $stock->update([
                        'qty' => $newQty,
                        'avg_cost' => max(0, round($newAvgCost, 2))
                    ]);
                }
            }
            // Delete ledger entries
            \App\Models\LedgerEntry::where('reference_type', 'opening_stock')->where('reference_id', $openingStock->id)->delete();
            $openingStock->delete();
        });

        $this->logActivity('Deleted Opening Stock', 'Opening Stocks', $openingStock->id, $openingStock->toArray(), null);

        return redirect()->route('opening-stocks.index')->with('success', 'Opening stock deleted and stock adjusted.');
    }

    public function exportPdf()
    {
        $openingStocks = OpeningStock::with(['product', 'warehouse'])->latest()->get();
        $settings = \App\Models\Setting::first();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('opening_stocks.export_pdf', compact('openingStocks', 'settings'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('opening-stocks-' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel()
    {
        $openingStocks = OpeningStock::with(['product', 'warehouse'])->latest()->get();

        $callback = function() use ($openingStocks) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Product', 'Warehouse', 'Qty', 'Rate', 'Cargo', 'Total', 'Note']);
            foreach ($openingStocks as $os) {
                fputcsv($file, [
                    $os->date,
                    $os->product->name,
                    $os->warehouse->name,
                    $os->qty,
                    $os->rate,
                    $os->cargo_amount,
                    $os->total,
                    $os->note,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="opening-stocks-' . date('Y-m-d') . '.csv"',
        ]);
    }
}
