<?php

namespace App\Services;

use App\Models\DashboardCardConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardCardService
{
    protected string $cacheKey = 'dashboard.card_configs';
    protected int $ttl = 3600;

    public function getAll(): iterable
    {
        $cached = Cache::get($this->cacheKey);

        if ($cached !== null && is_array($cached)) {
            return Collection::make(
                array_map(fn(array $item) => (object) $item, $cached)
            );
        }

        if ($cached !== null) {
            $this->flushCache();
        }

        $items = DashboardCardConfig::orderBy('sort_order')->get();
        Cache::put($this->cacheKey, $items->toArray(), $this->ttl);

        return $items;
    }

    public function getEnabledBySection(string $section): iterable
    {
        return $this->getAll()->where('section', $section)->where('is_enabled', true);
    }

    public function getEnabled(): iterable
    {
        return $this->getAll()->where('is_enabled', true);
    }

    public function toggle(string $key): bool
    {
        $card = DashboardCardConfig::where('key', $key)->first();
        if (!$card) {
            return false;
        }

        $card->update(['is_enabled' => !$card->is_enabled]);
        $this->flushCache();

        return true;
    }

    public function updateOrder(array $order): void
    {
        foreach ($order as $index => $key) {
            DashboardCardConfig::where('key', $key)->update(['sort_order' => $index]);
        }
        $this->flushCache();
    }

    public function resetDefaults(): void
    {
        $this->seedDefaults();
        $this->flushCache();
    }

    public static function seedDefaults(): void
    {
        $cards = [
            ['key' => 'total_products', 'title' => 'Total Products', 'icon' => 'box', 'color' => 'primary', 'section' => 'kpi', 'sort_order' => 0, 'is_enabled' => true],
            ['key' => 'today_sales', 'title' => "Today's Sales", 'icon' => 'currency-dollar', 'color' => 'success', 'section' => 'kpi', 'sort_order' => 1, 'is_enabled' => true],
            ['key' => 'monthly_revenue', 'title' => 'Monthly Revenue', 'icon' => 'chart-bar', 'color' => 'info', 'section' => 'kpi', 'sort_order' => 2, 'is_enabled' => true],
            ['key' => 'low_stock', 'title' => 'Low Stock Items', 'icon' => 'exclamation-triangle', 'color' => 'danger', 'section' => 'kpi', 'sort_order' => 3, 'is_enabled' => true],
            ['key' => 'stock_health', 'title' => 'Stock Health', 'icon' => 'heart', 'color' => 'success', 'section' => 'kpi', 'sort_order' => 4, 'is_enabled' => true],
            ['key' => 'credit_exposure', 'title' => 'Credit Exposure', 'icon' => 'credit-card', 'color' => 'warning', 'section' => 'kpi', 'sort_order' => 5, 'is_enabled' => true],
            ['key' => 'pending_purchases', 'title' => 'Pending Purchases', 'icon' => 'truck', 'color' => 'purple', 'section' => 'kpi', 'sort_order' => 6, 'is_enabled' => true],
            ['key' => 'active_suppliers', 'title' => 'Active Suppliers', 'icon' => 'people', 'color' => 'info', 'section' => 'kpi', 'sort_order' => 7, 'is_enabled' => true],
        ];

        foreach ($cards as $card) {
            DashboardCardConfig::updateOrCreate(
                ['key' => $card['key']],
                $card
            );
        }
    }

    public function flushCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
