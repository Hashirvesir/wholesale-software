<?php

namespace App\Http\Controllers;

use App\Models\CargoCharge;
use App\Models\Purchase;
use App\Models\Party;
use App\Models\ChartOfAccount;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\TracksActivity;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CargoChargeController extends Controller implements HasMiddleware
{
    use TracksActivity;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view cargo charges', only: ['index']),
            new Middleware('permission:create cargo charges', except: ['index']),
        ];
    }
    public function index()
    {
        $cargoCharges = CargoCharge::with(['purchase', 'cargoParty'])->latest()->paginate(10);
        return view('cargo_charges.index', compact('cargoCharges'));
    }

    public function create(Request $request)
    {
        $purchases = Purchase::with('party')->latest()->get()->map(function($p) {
            return [
                'id' => $p->id,
                'search' => "PUR-#{$p->id} ({$p->date}) - {$p->party->name}",
                'party_name' => $p->party->name,
                'date' => $p->date
            ];
        });
        $cargoParties = Party::where('type', 'cargo')->where('status', true)->get()->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'phone' => $p->phone
            ];
        });
        $selectedPurchaseId = $request->query('purchase_id');
        
        return view('cargo_charges.create', compact('purchases', 'cargoParties', 'selectedPurchaseId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id|unique:cargo_charges,purchase_id',
            'cargo_party_id' => 'required|exists:parties,id',
            'total_weight' => 'required|numeric|min:0',
            'rate_per_kg' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'is_paid' => 'nullable|boolean',
        ], [
            'purchase_id.unique' => 'Cargo charges have already been recorded for this purchase.',
        ]);

        $cargoCharge = DB::transaction(function () use ($request) {
            $cargoCharge = CargoCharge::create($request->all());

            $cargoExpenseAcc = ChartOfAccount::where('name', 'Cargo Expense')->first();
            $payableAcc = ChartOfAccount::where('name', 'Accounts Payable')->first();

            // Debit Cargo Expense
            LedgerEntry::create([
                'account_id' => $cargoExpenseAcc->id,
                'party_id' => null, // Internal expense account, no party association for ledger balance
                'debit' => $cargoCharge->total_amount,
                'credit' => 0,
                'date' => $cargoCharge->purchase->date ?? now()->toDateString(),
                'reference_type' => 'cargo',
                'reference_id' => $cargoCharge->id,
                'note' => "Cargo Charges for Purchase #{$cargoCharge->purchase_id}",
            ]);

            // Credit Cargo Party (we owe them - liability)
            LedgerEntry::create([
                'account_id' => $payableAcc->id,
                'party_id' => $cargoCharge->cargo_party_id,
                'debit' => 0,
                'credit' => $cargoCharge->total_amount,
                'date' => $cargoCharge->purchase->date ?? now()->toDateString(),
                'reference_type' => 'cargo',
                'reference_id' => $cargoCharge->id,
                'note' => "Cargo Charges for Purchase #{$cargoCharge->purchase_id}",
            ]);

            // 3. Payment Entry (if paid immediately)
            if ($request->boolean('is_paid')) {
                $cashAcc = ChartOfAccount::where('name', 'Cash')->first();
                
                // Debit Cargo Party (clearing the liability we owe)
                LedgerEntry::create([
                    'account_id' => $payableAcc->id,
                    'party_id' => $cargoCharge->cargo_party_id,
                    'debit' => $cargoCharge->total_amount,
                    'credit' => 0,
                    'date' => $cargoCharge->purchase->date ?? now()->toDateString(),
                    'reference_type' => 'cargo',
                    'reference_id' => $cargoCharge->id,
                    'note' => "Payment for Cargo - Purchase #{$cargoCharge->purchase_id}",
                ]);

                // Credit Cash
                LedgerEntry::create([
                    'account_id' => $cashAcc->id,
                    'party_id' => null,
                    'debit' => 0,
                    'credit' => $cargoCharge->total_amount,
                    'date' => $cargoCharge->purchase->date ?? now()->toDateString(),
                    'reference_type' => 'cargo',
                    'reference_id' => $cargoCharge->id,
                    'note' => "Payment for Cargo - Purchase #{$cargoCharge->purchase_id}",
                ]);
            }
            return $cargoCharge;
        });

        $this->logActivity('Recorded Cargo Charge', 'Cargo Charges', $cargoCharge->id, null, $cargoCharge->toArray());

        return redirect()->route('cargo-charges.index')->with('success', 'Cargo charges recorded successfully and ledger updated.');
    }

    public function edit(CargoCharge $cargoCharge)
    {
        $purchases = Purchase::with('party')->latest()->get()->map(function($p) {
            return [
                'id' => $p->id,
                'search' => "PUR-#{$p->id} ({$p->date}) - {$p->party->name}",
                'party_name' => $p->party->name,
                'date' => $p->date
            ];
        });
        $cargoParties = Party::where('type', 'cargo')->where('status', true)->get()->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'phone' => $p->phone
            ];
        });
        
        return view('cargo_charges.edit', compact('cargoCharge', 'purchases', 'cargoParties'));
    }

    public function update(Request $request, CargoCharge $cargoCharge)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id|unique:cargo_charges,purchase_id,' . $cargoCharge->id,
            'cargo_party_id' => 'required|exists:parties,id',
            'total_weight' => 'required|numeric|min:0',
            'rate_per_kg' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'is_paid' => 'nullable|boolean',
        ], [
            'purchase_id.unique' => 'Cargo charges already exist for this purchase.',
        ]);

        DB::transaction(function () use ($request, $cargoCharge) {
            $cargoCharge->update($request->all());

            // Delete old ledger entries
            LedgerEntry::where('reference_type', 'cargo')->where('reference_id', $cargoCharge->id)->delete();

            $cargoExpenseAcc = ChartOfAccount::where('name', 'Cargo Expense')->first();
            $payableAcc = ChartOfAccount::where('name', 'Accounts Payable')->first();

            // Re-create Debit Cargo Expense
            LedgerEntry::create([
                'account_id' => $cargoExpenseAcc->id,
                'party_id' => null, // Internal expense account, no party association for ledger balance
                'debit' => $cargoCharge->total_amount,
                'credit' => 0,
                'date' => $cargoCharge->purchase->date ?? now()->toDateString(),
                'reference_type' => 'cargo',
                'reference_id' => $cargoCharge->id,
                'note' => "Updated Cargo Charges for Purchase #{$cargoCharge->purchase_id}",
            ]);

            // Re-create Credit Cargo Party (we owe them - liability)
            LedgerEntry::create([
                'account_id' => $payableAcc->id,
                'party_id' => $cargoCharge->cargo_party_id,
                'debit' => 0,
                'credit' => $cargoCharge->total_amount,
                'date' => $cargoCharge->purchase->date ?? now()->toDateString(),
                'reference_type' => 'cargo',
                'reference_id' => $cargoCharge->id,
                'note' => "Updated Cargo Charges for Purchase #{$cargoCharge->purchase_id}",
            ]);

            // 3. Re-create Payment entries if paid
            if ($request->boolean('is_paid')) {
                $cashAcc = ChartOfAccount::where('name', 'Cash')->first();
                
                // Debit Cargo Party (clearing the liability we owe)
                LedgerEntry::create([
                    'account_id' => $payableAcc->id,
                    'party_id' => $cargoCharge->cargo_party_id,
                    'debit' => $cargoCharge->total_amount,
                    'credit' => 0,
                    'date' => $cargoCharge->purchase->date ?? now()->toDateString(),
                    'reference_type' => 'cargo',
                    'reference_id' => $cargoCharge->id,
                    'note' => "Payment for Cargo - Purchase #{$cargoCharge->purchase_id}",
                ]);

                // Credit Cash
                LedgerEntry::create([
                    'account_id' => $cashAcc->id,
                    'party_id' => null,
                    'debit' => 0,
                    'credit' => $cargoCharge->total_amount,
                    'date' => $cargoCharge->purchase->date ?? now()->toDateString(),
                    'reference_type' => 'cargo',
                    'reference_id' => $cargoCharge->id,
                    'note' => "Payment for Cargo - Purchase #{$cargoCharge->purchase_id}",
                ]);
            }
        });

        $this->logActivity('Updated Cargo Charge', 'Cargo Charges', $cargoCharge->id, null, $request->all());

        return redirect()->route('cargo-charges.index')->with('success', 'Cargo charges updated successfully.');
    }

    public function destroy(CargoCharge $cargoCharge)
    {
        $oldData = $cargoCharge->toArray();
        DB::transaction(function () use ($cargoCharge) {
            // Delete ledger entries
            LedgerEntry::where('reference_type', 'cargo')->where('reference_id', $cargoCharge->id)->delete();
            $cargoCharge->delete();
        });

        $this->logActivity('Deleted Cargo Charge', 'Cargo Charges', $cargoCharge->id, $oldData, null);

        return redirect()->route('cargo-charges.index')->with('success', 'Cargo charges deleted successfully.');
    }
}
