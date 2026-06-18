<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Group;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = Cache::remember('dashboard.stats.' . $user->id, 3600, function () {
            $products = Product::count();
            $totalProducts = $products;
            $totalCategories = Category::count();
            $totalUsers = User::count();
            $totalGroups = Group::count();
            $lowStock = Product::where('reorder_level', '>', 0)->count();
            $activeSuppliers = Supplier::where('is_active', true)->count();

            $stockHealth = $totalProducts > 0
                ? round((1 - $lowStock / max($totalProducts, 1)) * 100)
                : 100;

            return [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_users' => $totalUsers,
                'total_groups' => $totalGroups,
                'today_sales' => 0,
                'sales_change' => 0,
                'monthly_revenue' => 'TSh 0',
                'low_stock' => $lowStock,
                'stock_health' => $stockHealth,
                'credit_exposure' => 'TSh 0',
                'credit_customers' => 0,
                'pending_purchases' => 0,
                'active_suppliers' => $activeSuppliers,
            ];
        });

        $recentActivities = AuditLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::where('is_active', true)->get();
        $categoryNames = $categories->pluck('name')->toArray();
        $categoryCounts = $categories->map(function ($cat) {
            return $cat->products()->count();
        })->toArray();

        $chartLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'];
        $chartSales = [65, 78, 55, 90, 72];
        $chartRevenue = [45, 62, 48, 75, 58];

        return view('dashboard', compact(
            'stats', 'recentActivities',
            'categoryNames', 'categoryCounts',
            'chartLabels', 'chartSales', 'chartRevenue'
        ));
    }
}
