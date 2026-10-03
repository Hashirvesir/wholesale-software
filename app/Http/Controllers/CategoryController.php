<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\TracksActivity;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CategoryController extends Controller implements HasMiddleware
{
    use TracksActivity;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view categories', only: ['index']),
            new Middleware('permission:manage categories', except: ['index']),
        ];
    }
    public function index()
    {
        $categories = Category::with('parent')->latest()->paginate(10);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = Category::create($request->all());

        $this->logActivity('Created Category', 'Categories', $category->id, null, $category->toArray());

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->get();
        return view('categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $oldData = $category->toArray();
        $category->update($request->all());

        $this->logActivity('Updated Category', 'Categories', $category->id, $oldData, $category->toArray());

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete category associated with products or subcategories.');
        }
        $oldData = $category->toArray();
        $category->delete();
        $this->logActivity('Deleted Category', 'Categories', $category->id, $oldData, null);
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
