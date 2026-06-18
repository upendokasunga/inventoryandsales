<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class SkuService
{
    public function generate(Category $category): string
    {
        $prefix = $this->getCategoryCode($category);
        $lastSku = Product::where('sku', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->value('sku');

        if ($lastSku) {
            $parts = explode('-', $lastSku);
            $number = (int) end($parts) + 1;
        } else {
            $number = 1;
        }

        return $prefix . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    protected function getCategoryCode(Category $category): string
    {
        $code = '';
        $current = $category;

        while ($current) {
            $code = $this->abbreviate($current->name) . ($code ? '-' . $code : '');
            $current = $current->parent;
        }

        return $code ?: 'GEN';
    }

    protected function abbreviate(string $name): string
    {
        $words = preg_split('/[\s\-_\/]+/', $name);
        $abbr = '';

        foreach ($words as $word) {
            $abbr .= strtoupper(Str::substr($word, 0, 1));
        }

        return $abbr ?: 'GEN';
    }
}
