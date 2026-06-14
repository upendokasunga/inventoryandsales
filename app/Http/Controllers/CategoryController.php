<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
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

        $status = $request->get('status');
        if ($status === 'active') {
            $categories = $this->categoryService->getAllPaginated(null, ['is_active' => true]);
        } elseif ($status === 'inactive') {
            $categories = $this->categoryService->getAllPaginated(null, ['is_active' => false]);
        }

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

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        $parents = $this->categoryService->getParentOptions($category);

        return view('categories.edit', compact('category', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categoryService->update($category, $request->validated());

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
