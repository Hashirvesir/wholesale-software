<?php

namespace App\Http\Controllers;

use App\Models\RepairBrand;
use App\Models\RepairModel;
use Illuminate\Http\Request;

class RepairBrandController extends Controller
{
    /**
     * Display a listing of repair brands and models count.
     */
    public function index(Request $request)
    {
        $query = RepairBrand::withCount('models');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('name', 'like', "%{$search}%");
        }

        $totalBrands = RepairBrand::count();
        $totalModels = RepairModel::count();

        $brands = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();

        return view('repair-brands.index', compact('brands', 'totalBrands', 'totalModels'));
    }

    /**
     * Show the form for creating a new repair brand.
     */
    public function create()
    {
        return view('repair-brands.create');
    }

    /**
     * Store a newly created repair brand in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:repair_brands,name',
            'status' => 'required|boolean',
            'sort_order' => 'nullable|integer',
        ], [
            'name.required' => 'Brand ka naam likhna lazmi hai.',
            'name.unique' => 'Yeh brand pehle se mojood hai.',
        ]);

        $brand = RepairBrand::create([
            'name' => trim($request->name),
            'status' => (bool) $request->status,
            'sort_order' => intval($request->sort_order ?: ((RepairBrand::max('sort_order') ?? 0) + 1)),
        ]);

        return redirect()->route('repair-brands.edit', $brand->id)->with('success', 'Naya Brand "' . $brand->name . '" add ho gaya hai. Ab aap iske models shamil kar sakte hain.');
    }

    /**
     * Show the form for editing the specified repair brand and managing its phone models.
     */
    public function edit(RepairBrand $repair_brand, Request $request)
    {
        $brand = $repair_brand;
        
        $modelsQuery = $brand->models();
        if ($request->filled('model_search')) {
            $search = trim($request->model_search);
            $modelsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('series', 'like', "%{$search}%");
            });
        }

        $models = $modelsQuery->orderBy('series')->orderBy('sort_order')->orderBy('name')->get();
        $seriesList = $brand->models()->reorder()->select('series')->distinct()->pluck('series')->filter()->values();

        return view('repair-brands.edit', compact('brand', 'models', 'seriesList'));
    }

    /**
     * Update the specified repair brand in storage.
     */
    public function update(Request $request, RepairBrand $repair_brand)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:repair_brands,name,' . $repair_brand->id,
            'status' => 'required|boolean',
            'sort_order' => 'nullable|integer',
        ], [
            'name.required' => 'Brand ka naam likhna lazmi hai.',
        ]);

        $repair_brand->update([
            'name' => trim($request->name),
            'status' => (bool) $request->status,
            'sort_order' => intval($request->sort_order ?: $repair_brand->sort_order),
        ]);

        return redirect()->route('repair-brands.index')->with('success', 'Brand "' . $repair_brand->name . '" update ho gaya hai.');
    }

    /**
     * Remove the specified repair brand and its models from storage.
     */
    public function destroy(RepairBrand $repair_brand)
    {
        $name = $repair_brand->name;
        $repair_brand->models()->delete();
        $repair_brand->delete();

        return redirect()->route('repair-brands.index')->with('success', 'Brand "' . $name . '" aur iske tamam models delete kar diye gaye hain.');
    }

    /**
     * Store a new phone model under a specific brand.
     */
    public function storeModel(Request $request, RepairBrand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'series' => 'nullable|string|max:100',
        ], [
            'name.required' => 'Model ka naam likhna lazmi hai.',
        ]);

        $model = RepairModel::firstOrCreate(
            [
                'repair_brand_id' => $brand->id,
                'name' => trim($request->name),
            ],
            [
                'series' => trim($request->series ?: 'General Series'),
                'status' => true,
                'sort_order' => (RepairModel::where('repair_brand_id', $brand->id)->max('sort_order') ?? 0) + 1,
            ]
        );

        return redirect()->route('repair-brands.edit', $brand->id)->with('success', 'Naya Model "' . $model->name . '" shamil kar diya gaya hai.');
    }

    /**
     * Delete a specific phone model.
     */
    public function destroyModel(RepairModel $model)
    {
        $brandId = $model->repair_brand_id;
        $name = $model->name;
        $model->delete();

        return redirect()->route('repair-brands.edit', $brandId)->with('success', 'Model "' . $name . '" delete kar diya gaya hai.');
    }
}
