<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Traits\TracksActivity;

class WarehouseController extends Controller
{
    use TracksActivity;
    public function index()
    {
        $warehouses = Warehouse::latest()->paginate(10);
        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);

        $warehouse = Warehouse::create($request->all());

        $this->logActivity('Created Warehouse', 'Warehouses', $warehouse->id, null, $warehouse->toArray());

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);

        $oldData = $warehouse->toArray();
        $warehouse->update($request->all());

        $this->logActivity('Updated Warehouse', 'Warehouses', $warehouse->id, $oldData, $warehouse->toArray());

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->stocks()->where('qty', '!=', 0)->exists() || $warehouse->sales()->exists() || $warehouse->purchases()->exists()) {
            return redirect()->route('warehouses.index')->with('error', 'Cannot delete warehouse with existing stocks or transactions.');
        }
        $oldData = $warehouse->toArray();
        $warehouse->delete();
        $this->logActivity('Deleted Warehouse', 'Warehouses', $warehouse->id, $oldData, null);
        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }
}
