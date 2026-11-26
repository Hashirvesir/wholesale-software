<?php

namespace App\Http\Controllers;

use App\Models\Salesman;
use Illuminate\Http\Request;
use App\Traits\TracksActivity;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SalesmanController extends Controller implements HasMiddleware
{
    use TracksActivity;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view salesmen|manage salesmen|manage users|manage parties', only: ['index']),
            new Middleware('permission:manage salesmen|manage users|manage parties', except: ['index']),
        ];
    }

    public function index(Request $request)
    {
        $query = Salesman::withCount(['customers', 'sales']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === '1');
        }

        $salesmen = $query->latest()->paginate(15)->withQueryString();

        return view('salesmen.index', compact('salesmen'));
    }

    public function create()
    {
        return view('salesmen.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|boolean',
        ]);

        $validated['commission_rate'] = $validated['commission_rate'] ?? 0.00;

        $salesman = Salesman::create($validated);

        $this->logActivity('Created Salesman', 'Salesmen', $salesman->id, null, $salesman->toArray());

        return redirect()->route('salesmen.index')->with('success', 'Salesman added successfully.');
    }

    public function edit(Salesman $salesman)
    {
        return view('salesmen.edit', compact('salesman'));
    }

    public function update(Request $request, Salesman $salesman)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|boolean',
        ]);

        $validated['commission_rate'] = $validated['commission_rate'] ?? 0.00;

        $salesman->update($validated);

        $this->logActivity('Updated Salesman', 'Salesmen', $salesman->id, null, $salesman->toArray());

        return redirect()->route('salesmen.index')->with('success', 'Salesman updated successfully.');
    }

    public function destroy(Salesman $salesman)
    {
        $customerCount = $salesman->customers()->count();
        $salesCount = $salesman->sales()->count();

        $name = $salesman->name;
        $salesman->delete();

        $this->logActivity('Deleted Salesman', 'Salesmen', null, null, ['name' => $name]);

        return redirect()->route('salesmen.index')->with('success', "Salesman '{$name}' deleted successfully.");
    }
}
