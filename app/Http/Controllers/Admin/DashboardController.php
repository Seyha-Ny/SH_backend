<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $range = (string) request('range', '7d');

        $rawFrom = request('from');
        $rawTo = request('to');

        $from = $rawFrom ? \Carbon\Carbon::parse($rawFrom)->startOfDay() : null;
        $to = $rawTo ? \Carbon\Carbon::parse($rawTo)->endOfDay() : null;

        if (! $from && ! $to) {
            $to = now()->endOfDay();
            $from = (match ($range) {
                '30d' => now()->subDays(30),
                '90d' => now()->subDays(90),
                default => now()->subDays(7),
            })->startOfDay();
        }

        if (! $from) {
            $from = (clone $to)->subDays(7)->startOfDay();
        }

        if (! $to) {
            $to = (clone $from)->addDays(7)->endOfDay();
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $statusBreakdown = Order::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $ordersQuery = Order::query()->whereBetween('created_at', [$from, $to]);

        $days = [];
        for ($i = 0; $i <= $from->diffInDays($to); $i++) {
            $date = (clone $from)->addDays($i)->toDateString();
            $days[$date] = (int) $ordersQuery->clone()->whereDate('created_at', $date)->count();
        }
        $weekly = $days;

        $revenueQuery = Order::query()->whereBetween('created_at', [$from, $to]);

        $revenueDays = [];
        foreach ($weekly as $date => $_) {
            $revenueDays[$date] = (float) $revenueQuery->clone()->whereDate('created_at', $date)->sum('total');
        }
        $revenueWeekly = $revenueDays;

        $topProducts = Product::query()
            ->select('products.id', 'products.name', DB::raw('sum(order_items.quantity) as units_sold'), DB::raw('sum(order_items.quantity * order_items.price) as revenue'))
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $topCategories = Category::query()
            ->select('categories.id', 'categories.name', DB::raw('count(products.id) as total_products'), DB::raw('sum(order_items.quantity * order_items.price) as revenue'))
            ->leftJoin('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $lowStockProducts = Product::where('stock', '<=', 5)->orderBy('stock')->limit(10)->get();

        $this->notifyLowStockTelegram($lowStockProducts);

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $todayOrders = (int) Order::whereDate('created_at', $today)->count();
        $yesterdayOrders = (int) Order::whereDate('created_at', $yesterday)->count();

        $todayRevenue = (float) Order::whereDate('created_at', $today)->sum('total');
        $yesterdayRevenue = (float) Order::whereDate('created_at', $yesterday)->sum('total');

        $startOfWeek = now()->startOfWeek()->toDateString();
        $startOfLastWeek = now()->subWeek()->startOfWeek()->toDateString();
        $endOfLastWeek = now()->subWeek()->endOfWeek()->toDateString();

        $thisWeekOrders = (int) Order::whereDate('created_at', '>=', $startOfWeek)->count();
        $lastWeekOrders = (int) Order::whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])->count();

        $thisWeekRevenue = (float) Order::whereDate('created_at', '>=', $startOfWeek)->sum('total');
        $lastWeekRevenue = (float) Order::whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])->sum('total');

        $thisWeekUsers = (int) User::whereDate('created_at', '>=', $startOfWeek)->count();
        $lastWeekUsers = (int) User::whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])->count();

        $todayUsers = (int) User::whereDate('created_at', '>=', $today)->count();
        $yesterdayUsers = (int) User::whereDate('created_at', '=', $yesterday)->count();

        $pendingReviews = (int) Review::where('approved', false)->count();
        $pendingCancellations = (int) Order::where('status', 'cancellation_requested')->count();
        $pendingReturns = (int) Order::where('status', 'return_requested')->count();

        $stats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'orders' => Order::count(),
            'users' => User::count(),
            'revenue' => (float) Order::sum('total'),
        ];

        $monthlyRevenue = (float) Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $monthlyTarget = (float) env('MONTHLY_REVENUE_TARGET', 10000);

        $monthlyProgress = $monthlyTarget > 0 ? min(100, ($monthlyRevenue / $monthlyTarget) * 100) : 0;

        $recentCustomers = User::query()
            ->select('users.id', 'users.name', 'users.email', DB::raw('SUM(orders.total) as total_spent'), DB::raw('MAX(orders.created_at) as latest_order_at'))
            ->leftJoin('orders', 'orders.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('latest_order_at')
            ->take(8)
            ->get();

        $activities = Activity::with('user')->latest()->take(20)->get();

        $systemHealth = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'app_env' => app()->environment(),
            'debug' => Config::get('app.debug') ? 'On' : 'Off',
            'cache_driver' => Config::get('cache.default'),
            'queue_connection' => Config::get('queue.default'),
            'database_connection' => Config::get('database.default'),
            'db_connected' => false,
        ];

        try {
            DB::connection()->getPdo();
            $systemHealth['db_connected'] = true;
        } catch (\Throwable $e) {
            $systemHealth['db_connected'] = false;
        }

        $cacheOk = false;
        try {
            Cache::put('__health_check__', true, 10);
            $cacheOk = Cache::get('__health_check__') === true;
            Cache::forget('__health_check__');
        } catch (\Throwable $e) {
            $cacheOk = false;
        }

        $systemHealth['cache_ok'] = $cacheOk;

        $storagePath = storage_path('app');
        $freeMb = null;
        if (is_dir($storagePath) && function_exists('disk_free_space')) {
            $freeMb = round(disk_free_space($storagePath) / (1024 * 1024), 1);
        }
        $systemHealth['storage_free_mb'] = $freeMb;

        return view('admin.dashboard.index', [
            'stats' => $stats,
            'recentOrders' => Order::latest()->take(10)->get(),
            'statusBreakdown' => $statusBreakdown,
            'weeklyOrders' => $weekly,
            'revenueWeekly' => $revenueWeekly,
            'topProducts' => $topProducts,
            'topCategories' => $topCategories,
            'lowStockProducts' => $lowStockProducts,
            'ordersTrend' => $this->buildTrend($thisWeekOrders, $lastWeekOrders),
            'revenueTrend' => $this->buildTrend($thisWeekRevenue, $lastWeekRevenue),
            'usersTrend' => $this->buildTrend($thisWeekUsers, $lastWeekUsers),
            'recentCustomers' => $recentCustomers,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyTarget' => $monthlyTarget,
            'monthlyProgress' => $monthlyProgress,
            'activities' => $activities,
            'systemHealth' => $systemHealth,
            'pendingReviews' => $pendingReviews,
            'pendingCancellations' => $pendingCancellations,
            'pendingReturns' => $pendingReturns,
            'from' => $from,
            'to' => $to,
        ]);
    }

    private function buildTrend(float|int|string $current, float|int|string $previous): array
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous <= 0) {
            $direction = $current > 0 ? 'up' : 'neutral';
            $change = $current;
        } else {
            $change = $current - $previous;
            $direction = $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral');
        }

        return [
            'current' => $current,
            'previous' => $previous,
            'change' => $change,
            'direction' => $direction,
        ];
    }

    private function notifyLowStockTelegram($lowStockProducts): void
    {
        if (! config('services.telegram.bot_token') || $lowStockProducts->isEmpty()) {
            return;
        }

        $cacheKey = 'telegram.low_stock_alert_sent';
        if (Cache::get($cacheKey)) {
            return;
        }

        $lines = ['⚠️ Low stock alert:'];
        foreach ($lowStockProducts as $product) {
            $lines[] = '- ' . $product->name . ' (stock: ' . $product->stock . ')';
        }

        $text = implode(PHP_EOL, $lines);

        $sent = app(\App\Services\TelegramService::class)->sendToAdminChat($text);

        // Only suppress repeats when the alert actually reached Telegram;
        // otherwise retry on the next dashboard visit.
        if ($sent) {
            Cache::put($cacheKey, true, now()->addHours(6));
        }
    }
}
