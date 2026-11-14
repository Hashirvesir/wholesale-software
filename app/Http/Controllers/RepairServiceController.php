<?php

namespace App\Http\Controllers;

use App\Models\RepairService;
use Illuminate\Http\Request;

class RepairServiceController extends Controller
{
    /**
     * Display a listing of repair services and rates.
     */
    public function index(Request $request)
    {
        $query = RepairService::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $categories = RepairService::select('category')->distinct()->pluck('category')->filter()->values();
        $totalServices = RepairService::count();
        $avgRate = RepairService::avg('default_labor_cost') ?? 0;

        $services = $query->orderBy('category')->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();

        return view('repair-services.index', compact('services', 'categories', 'totalServices', 'avgRate'));
    }

    /**
     * Show the form for creating a new repair service.
     */
    public function create()
    {
        $categories = RepairService::select('category')->distinct()->pluck('category')->filter()->values();
        if ($categories->isEmpty()) {
            $categories = collect(['Screen & Display', 'Battery & Charging', 'Camera & Audio', 'Board & Motherboard', 'Software & OS', 'Body & Housing', 'General Repair']);
        }

        return view('repair-services.create', compact('categories'));
    }

    /**
     * Store a newly created repair service in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:repair_services,name',
            'category' => 'nullable|string|max:100',
            'default_labor_cost' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'status' => 'required|boolean',
            'sort_order' => 'nullable|integer',
        ], [
            'name.required' => 'Service ka naam likhna lazmi hai.',
            'name.unique' => 'Yeh service pehle se mojood hai.',
            'default_labor_cost.required' => 'Default labor rate lazmi likhein.',
        ]);

        $service = RepairService::create([
            'name' => trim($request->name),
            'category' => trim($request->category ?: 'General Repair'),
            'default_labor_cost' => floatval($request->default_labor_cost),
            'description' => $request->description,
            'status' => (bool) $request->status,
            'sort_order' => intval($request->sort_order ?: ((RepairService::max('sort_order') ?? 0) + 1)),
        ]);

        return redirect()->route('repair-services.index')->with('success', 'Nayi repair service "' . $service->name . '" kamiyabi se add ho gayi hai.');
    }

    /**
     * Show the form for editing the specified repair service.
     */
    public function edit(RepairService $repair_service)
    {
        $service = $repair_service;
        $categories = RepairService::select('category')->distinct()->pluck('category')->filter()->values();

        return view('repair-services.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified repair service in storage.
     */
    public function update(Request $request, RepairService $repair_service)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:repair_services,name,' . $repair_service->id,
            'category' => 'nullable|string|max:100',
            'default_labor_cost' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'status' => 'required|boolean',
            'sort_order' => 'nullable|integer',
        ], [
            'name.required' => 'Service ka naam likhna lazmi hai.',
            'default_labor_cost.required' => 'Default labor rate lazmi likhein.',
        ]);

        $repair_service->update([
            'name' => trim($request->name),
            'category' => trim($request->category ?: 'General Repair'),
            'default_labor_cost' => floatval($request->default_labor_cost),
            'description' => $request->description,
            'status' => (bool) $request->status,
            'sort_order' => intval($request->sort_order ?: $repair_service->sort_order),
        ]);

        return redirect()->route('repair-services.index')->with('success', 'Service "' . $repair_service->name . '" kamiyabi se update ho gayi hai.');
    }

    /**
     * Remove the specified repair service from storage.
     */
    public function destroy(RepairService $repair_service)
    {
        $name = $repair_service->name;
        $repair_service->delete();

        return redirect()->route('repair-services.index')->with('success', 'Service "' . $name . '" delete kar di gayi hai.');
    }
}
