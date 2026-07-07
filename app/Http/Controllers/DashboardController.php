<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StoreRequest;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Services\DashboardCardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardCardService $cardService
    ) {}

    public function index()
    {
        $user = Auth::user();

        $stats = Cache::remember('dashboard.stats.' . $user->id, 300, function () {
            $totalProducts = Product::count();
            $totalCategories = Category::count();
            $totalUsers = User::count();
            $totalGroups = Group::count();
            $lowStock = Product::where('reorder_level', '>', 0)
                ->where('current_stock', '<=', DB::raw('reorder_level'))
                ->count();

            $todaySales = Invoice::whereDate('created_at', today())
                ->whereIn('status', ['posted', 'completed', 'paid'])
                ->sum('total');

            $yesterdaySales = Invoice::whereDate('created_at', today()->subDay())
                ->whereIn('status', ['posted', 'completed', 'paid'])
                ->sum('total');

            $salesChange = $yesterdaySales > 0
                ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100)
                : ($todaySales > 0 ? 100 : 0);

            $monthlyRevenue = Invoice::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereIn('status', ['posted', 'completed', 'paid'])
                ->sum('total');

            $stockHealth = $totalProducts > 0
                ? round((1 - $lowStock / max($totalProducts, 1)) * 100)
                : 100;
            $creditExposure = Customer::sum('outstanding_balance') ?? 0;

            $creditCustomers = Customer::where('outstanding_balance', '>', 0)->count();

            $pendingPurchases = PurchaseOrder::whereIn('status', ['draft', 'pending_approval', 'approved', 'sent', 'partially_received'])->count();
            $activeSuppliers = Supplier::where('is_active', true)->count();

            return [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_users' => $totalUsers,
                'total_groups' => $totalGroups,
                'today_sales' => $todaySales,
                'sales_change' => $salesChange,
                'monthly_revenue' => $monthlyRevenue,
                'low_stock' => $lowStock,
                'stock_health' => $stockHealth,
                'credit_exposure' => $creditExposure,
                'credit_customers' => $creditCustomers,
                'pending_purchases' => $pendingPurchases,
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

        $chartLabels = [];
        $chartSales = [];
        $chartRevenue = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $chartLabels[] = $date->format('M d');
            $chartSales[] = Invoice::whereDate('created_at', $date)
                ->whereIn('status', ['posted', 'completed', 'paid'])
                ->sum('total');
            $chartRevenue[] = Invoice::whereDate('created_at', $date)
                ->whereIn('status', ['posted', 'completed', 'paid'])
                ->sum('amount_paid');
        }

        $enabledCards = $this->cardService->getEnabledBySection('kpi')
            ->pluck('key')
            ->toArray();

        return view('dashboard', compact(
            'stats', 'recentActivities',
            'categoryNames', 'categoryCounts',
            'chartLabels', 'chartSales', 'chartRevenue',
            'enabledCards'
        ));
    }
}
