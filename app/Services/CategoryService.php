<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryService
{
    public function getAllPaginated(int $perPage = 20, ?array $filters = null)
    {
        $query = Category::with('parent');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getTree()
    {
        return Category::with('children.children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
    }

    public function search(string $query, int $perPage = 20)
    {
        return Category::with('parent')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Category
    {
        $data['slug'] = Str::slug($data['name']);

        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function getParentOptions(?Category $exclude = null)
    {
        $query = Category::whereNull('parent_id');

        if ($exclude) {
            $query->where('id', '!=', $exclude->id);
        }

        return $query->orderBy('name')->get();
    }
}
