<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use App\Traits\TracksActivity;

class UnitController extends Controller
{
    use TracksActivity;
    public function index()
    {
        $units = Unit::latest()->paginate(10);
        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'base_unit' => 'nullable|string|max:255',
            'conversion_value' => 'nullable|numeric|min:0',
        ]);

        $unit = Unit::create($request->all());

        $this->logActivity('Created Unit', 'Units', $unit->id, null, $unit->toArray());

        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'base_unit' => 'nullable|string|max:255',
            'conversion_value' => 'nullable|numeric|min:0',
        ]);

        $oldData = $unit->toArray();
        $unit->update($request->all());

        $this->logActivity('Updated Unit', 'Units', $unit->id, $oldData, $unit->toArray());

        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->exists()) {
            return redirect()->route('units.index')->with('error', 'Cannot delete unit associated with products.');
        }
        $oldData = $unit->toArray();
        $unit->delete();
        $this->logActivity('Deleted Unit', 'Units', $unit->id, $oldData, null);
        return redirect()->route('units.index')->with('success', 'Unit deleted successfully.');
    }
}
