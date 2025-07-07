<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Inertia\Inertia;
use Illuminate\Validation\Rule;


class CategoryController extends Controller
{

    public function index()
    {
        return Inertia::render('settings/categories/Index', [
            'categories' => Category::latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:225|unique:categories,name'
        ]);

        Category::create($validated);

        return back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, Category $category)
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],

        ]);

        $category->update($validated);

        return back()->with('success', 'Categories updated successfully!');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Category deleted successfully!');
    }
}
