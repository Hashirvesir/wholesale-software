<?php

namespace App\Http\Controllers;

use App\Models\Party;
use Illuminate\Http\Request;
use App\Traits\TracksActivity;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PartyController extends Controller implements HasMiddleware
{
    use TracksActivity;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view parties', only: ['index']),
            new Middleware('permission:manage parties', except: ['index']),
        ];
    }
    public function index(Request $request)
    {
        $query = Party::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        $parties = $query->latest()->paginate(10)->withQueryString();
        return view('parties.index', compact('parties'));
    }

    public function create()
    {
        return view('parties.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,vendor,cargo',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'opening_balance' => 'required|numeric',
            'balance_type' => 'required|in:debit,credit',
            'status' => 'required|boolean',
        ]);

        $party = Party::create($request->all());

        $this->logActivity('Created Party', 'Parties', $party->id, null, $party->toArray());

        return redirect()->route('parties.index')->with('success', 'Party created successfully.');
    }

    public function edit(Party $party)
    {
        return view('parties.edit', compact('party'));
    }

    public function update(Request $request, Party $party)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,vendor,cargo',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'opening_balance' => 'required|numeric',
            'balance_type' => 'required|in:debit,credit',
            'status' => 'required|boolean',
        ]);

        $oldData = $party->toArray();
        $party->update($request->all());

        $this->logActivity('Updated Party', 'Parties', $party->id, $oldData, $party->toArray());

        return redirect()->route('parties.index')->with('success', 'Party updated successfully.');
    }

    public function destroy(Party $party)
    {
        if ($party->sales()->exists() || $party->purchases()->exists() || \App\Models\LedgerEntry::where('party_id', $party->id)->exists()) {
            return redirect()->route('parties.index')->with('error', 'Cannot delete party with existing transactions.');
        }
        $oldData = $party->toArray();
        $party->delete();
        $this->logActivity('Deleted Party', 'Parties', $party->id, $oldData, null);
        return redirect()->route('parties.index')->with('success', 'Party deleted successfully.');
    }

    public function exportPdf(Request $request)
    {
        $query = Party::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        $parties = $query->latest()->get();
        $settings = \App\Models\Setting::first();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('parties.export_pdf', compact('parties', 'settings'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('parties-list-' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = Party::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        $parties = $query->latest()->get();

        $callback = function() use ($parties) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Phone', 'Address', 'Type', 'Opening Balance', 'Balance Type', 'Current Balance', 'Balance Direction', 'Status']);
            foreach ($parties as $party) {
                $currentBal = $party->getCurrentBalance();
                fputcsv($file, [
                    $party->name,
                    $party->phone,
                    $party->address,
                    ucfirst($party->type),
                    $party->opening_balance,
                    ucfirst($party->balance_type),
                    number_format(abs($currentBal), 2),
                    $currentBal >= 0 ? 'Dr' : 'Cr',
                    $party->status ? 'Active' : 'Inactive',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="parties-list-' . date('Y-m-d') . '.csv"',
        ]);
    }
}
