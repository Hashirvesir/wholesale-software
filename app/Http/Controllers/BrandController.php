<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Traits\TracksActivity;

class BrandController extends Controller
{
    use TracksActivity;
    public function index()
    {
        $brands = Brand::latest()->paginate(10);
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $brand = Brand::create($request->all());

        $this->logActivity('Created Brand', 'Brands', $brand->id, null, $brand->toArray());

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $oldData = $brand->toArray();
        $brand->update($request->all());

        $this->logActivity('Updated Brand', 'Brands', $brand->id, $oldData, $brand->toArray());

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return redirect()->route('brands.index')->with('error', 'Cannot delete brand associated with products.');
        }
        $oldData = $brand->toArray();
        $brand->delete();
        $this->logActivity('Deleted Brand', 'Brands', $brand->id, $oldData, null);
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}
