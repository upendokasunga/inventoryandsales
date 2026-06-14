<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request): View
    {
        $search = $request->get('search');

        $categories = $search
            ? $this->categoryService->search($search)
            : $this->categoryService->getAllPaginated();

        return view('categories.index', compact('categories', 'search'));
    }

    public function tree(): View
    {
        $categories = $this->categoryService->getTree();

        return view('categories.tree', compact('categories'));
    }

    public function create(): View
    {
        $parents = $this->categoryService->getParentOptions();

        return view('categories.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $this->categoryService->create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        $parents = $this->categoryService->getParentOptions($category);

        return view('categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id,
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $this->categoryService->update($category, $validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete category with subcategories.');
        }

        $this->categoryService->delete($category);

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
