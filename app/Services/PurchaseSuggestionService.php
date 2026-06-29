<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseSuggestion;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class PurchaseSuggestionService
{
    public function getAllPaginated(int $perPage = 20, ?array $filters = null): LengthAwarePaginator
    {
        $query = PurchaseSuggestion::with(['product', 'supplier', 'creator']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function generateSuggestions(?int $productId = null): array
    {
        $query = Product::with('category')
            ->where('track_stock', true)
            ->where('reorder_level', '>', 0);

        if ($productId) {
            $query->where('id', $productId);
        }

        $products = $query->get();
        $existingProductIds = PurchaseSuggestion::whereIn('product_id', $products->pluck('id'))
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('product_id')
            ->toArray();
        $created = [];

        foreach ($products as $product) {
            if (in_array($product->id, $existingProductIds)) {
                continue;
            }

            if ($product->current_stock <= $product->reorder_level) {
                $suggestedQty = max(0, $product->reorder_level - $product->current_stock + $product->safety_stock);

                $suggestion = PurchaseSuggestion::create([
                    'product_id' => $product->id,
                    'suggested_quantity' => $suggestedQty,
                    'reason' => vsprintf(
                        'Stock %s is below reorder level %s. Current: %s, Safety: %s',
                        [$product->current_stock, $product->reorder_level, $product->current_stock, $product->safety_stock]
                    ),
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                ]);
                $created[] = $suggestion;
            }
        }

        Cache::forget('purchasing.suggestion.stats');

        return $created;
    }

    public function approve(PurchaseSuggestion $suggestion): PurchaseSuggestion
    {
        $suggestion->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Cache::forget('purchasing.suggestion.stats');

        return $suggestion->fresh();
    }

    public function reject(PurchaseSuggestion $suggestion, ?string $notes = null): PurchaseSuggestion
    {
        $suggestion->update([
            'status' => 'rejected',
            'notes' => $notes ?? $suggestion->notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Cache::forget('purchasing.suggestion.stats');

        return $suggestion->fresh();
    }

    public function markConverted(PurchaseSuggestion $suggestion): PurchaseSuggestion
    {
        $suggestion->update(['status' => 'converted']);

        Cache::forget('purchasing.suggestion.stats');

        return $suggestion->fresh();
    }

    public function getStats(): array
    {
        return Cache::remember('purchasing.suggestion.stats', 3600, function () {
            return [
                'total' => PurchaseSuggestion::count(),
                'pending' => PurchaseSuggestion::where('status', 'pending')->count(),
                'approved' => PurchaseSuggestion::where('status', 'approved')->count(),
                'converted' => PurchaseSuggestion::where('status', 'converted')->count(),
                'rejected' => PurchaseSuggestion::where('status', 'rejected')->count(),
            ];
        });
    }

    public function create(array $data): PurchaseSuggestion
    {
        $data['created_by'] = auth()->id();
        $data['status'] = 'pending';

        return PurchaseSuggestion::create($data);
    }
}
