@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="h3 mb-0">Dashboard</h1>
            <div class="page-subtitle">Welcome back, {{ Auth::user()?->name ?: 'Admin' }} · {{ now()->format('l, F j') }}</div>
        </div>
    </div>

    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label class="form-label small text-muted">Range</label>
            <select class="form-select" name="range" onchange="this.form.submit()">
                <option value="7d" @selected(request('range', '7d') === '7d')>Last 7 days</option>
                <option value="30d" @selected(request('range') === '30d')>Last 30 days</option>
                <option value="90d" @selected(request('range') === '90d')>Last 90 days</option>
                <option value="custom" @selected(request('range') === 'custom')>Custom</option>
            </select>
        </div>
        @if (request('range') === 'custom')
        <div class="col-auto">
            <label class="form-label small text-muted">From</label>
            <input type="date" class="form-control" name="from" value="{{ request('from') }}">
        </div>
        <div class="col-auto">
            <label class="form-label small text-muted">To</label>
            <input type="date" class="form-control" name="to" value="{{ request('to') }}">
        </div>
        @endif
        <div class="col-auto">
            <button class="btn btn-primary" type="submit">Apply</button>
        </div>
        <div class="col-auto">
            <a class="btn btn-outline-secondary" href="{{ route('admin.dashboard') }}">Reset</a>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-box-seam"></i></div>
                <div class="label">Total Products</div>
                <div class="value">{{ $stats['products'] }}</div>
                <div class="quick-action"><a href="{{ route('admin.products.create') }}">+ Add Product</a></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon gold"><i class="bi bi-folder2-open"></i></div>
                <div class="label">Total Categories</div>
                <div class="value">{{ $stats['categories'] }}</div>
                <div class="quick-action"><a href="{{ route('admin.categories.index') }}">Manage Categories</a></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon indigo"><i class="bi bi-receipt"></i></div>
                <div class="label">Total Orders</div>
                <div class="value">{{ $stats['orders'] }}</div>
                @php
                    $orderTrend = $ordersTrend ?? null;
                @endphp
                @isset($orderTrend)
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <span class="badge {{ $orderTrend['direction'] === 'up' ? 'bg-success-subtle text-success' : ($orderTrend['direction'] === 'down' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary') }}">
                            <i class="bi bi-arrow-{{ $orderTrend['direction'] === 'up' ? 'up' : ($orderTrend['direction'] === 'down' ? 'down' : 'dash') }}"></i>
                            {{ abs((int) $orderTrend['change']) }}
                            <span class="ms-1 d-none d-sm-inline">vs last week</span>
                        </span>
                    </div>
                @endisset
                <div class="quick-action"><a href="{{ route('admin.orders.index') }}">View Orders</a></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-people"></i></div>
                <div class="label">Total Users</div>
                <div class="value">{{ $stats['users'] }}</div>
                @php
                    $userTrend = $usersTrend ?? null;
                @endphp
                @isset($userTrend)
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <span class="badge {{ $userTrend['direction'] === 'up' ? 'bg-success-subtle text-success' : ($userTrend['direction'] === 'down' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary') }}">
                            <i class="bi bi-arrow-{{ $userTrend['direction'] === 'up' ? 'up' : ($userTrend['direction'] === 'down' ? 'down' : 'dash') }}"></i>
                            {{ abs((int) $userTrend['change']) }}
                            <span class="ms-1 d-none d-sm-inline">vs last week</span>
                        </span>
                    </div>
                @endisset
                <div class="quick-action"><a href="{{ route('admin.users.index') }}">View Users</a></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                <div class="label">Total Revenue</div>
                <div class="value text-success">${{ number_format($stats['revenue'], 2) }}</div>
                @php
                    $rev = is_array($revenueTrend ?? null) ? $revenueTrend : null;
                @endphp
                @isset($rev)
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <span class="badge {{ $rev['direction'] === 'up' ? 'bg-success-subtle text-success' : ($rev['direction'] === 'down' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary') }}">
                            <i class="bi bi-arrow-{{ $rev['direction'] === 'up' ? 'up' : ($rev['direction'] === 'down' ? 'down' : 'dash') }}"></i>
                            ${{ number_format(abs($rev['change']), 2) }}
                            <span class="ms-1 d-none d-sm-inline">vs last week</span>
                        </span>
                    </div>
                @endisset
                <div class="quick-action"><a href="{{ route('admin.orders.index') }}">View Orders</a></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="low-stock-card">
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2">
                    <h2 class="h6 mb-0">Monthly Revenue Target</h2>
                    <span class="badge bg-info-subtle text-info">{{ now()->format('F Y') }}</span>
                </div>
                <div class="p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-medium">Progress</span>
                        <span class="fw-medium">{{ number_format($monthlyProgress, 1) }}%</span>
                    </div>
                    <div class="progress" style="height: 1.25rem;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $monthlyProgress }}%;"
                             aria-valuenow="{{ number_format($monthlyProgress, 1) }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 text-muted" style="font-size: .85rem;">
                        <span>${{ number_format($monthlyRevenue, 2) }} raised</span>
                        <span>Target ${{ number_format($monthlyTarget, 2) }}</span>
                    </div>
                    @if ($monthlyProgress < 100)
                        <div class="text-muted mt-2" style="font-size: .85rem;">
                            {{ number_format(100 - $monthlyProgress, 1) }}% remaining to reach target
                        </div>
                    @else
                        <div class="text-success mt-2" style="font-size: .85rem;">
                            Monthly target reached!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($lowStockProducts->count())
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="low-stock-card">
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2">
                        <h2 class="h6 mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock</h2>
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.products.index') }}">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th class="text-end">Stock</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($lowStockProducts as $product)
                                <tr>
                                    <td class="fw-medium">{{ $product->name }}</td>
                                    <td>{{ $product->category?->name ?? '—' }}</td>
                                    <td class="text-end">{{ $product->stock }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-danger-subtle text-danger">Low</span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-5">
            <div class="table-card">
                <div class="p-3 border-bottom"><h2 class="h6 mb-0">Order Status</h2></div>
                <div class="p-3">
                    <div style="max-width: 280px; margin: 0 auto;">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="table-card">
                <div class="p-3 border-bottom"><h2 class="h6 mb-0">Orders ({{ \Carbon\Carbon::parse($from)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($to)->format('Y-m-d') }})</h2></div>
                <div class="p-3">
                    <canvas id="weeklyOrdersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="table-card">
                <div class="p-3 border-bottom"><h2 class="h6 mb-0">Revenue ({{ \Carbon\Carbon::parse($from)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($to)->format('Y-m-d') }})</h2></div>
                <div class="p-3">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="table-card">
                <div class="p-3 border-bottom"><h2 class="h6 mb-0">Top Products</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Units</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($topProducts as $product)
                            <tr>
                                <td class="fw-medium">{{ $product->name }}</td>
                                <td class="text-center">{{ $product->units_sold }}</td>
                                <td class="text-end">${{ number_format($product->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No sales data yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="table-card">
                <div class="p-3 border-bottom"><h2 class="h6 mb-0">Top Categories</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-center">Products</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($topCategories as $category)
                            <tr>
                                <td class="fw-medium">{{ $category->name }}</td>
                                <td class="text-center">{{ $category->total_products }}</td>
                                <td class="text-end">${{ number_format((float) $category->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No category sales data yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2">
            <h2 class="h6 mb-0">Recent Orders</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($recentOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->user?->name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}</td>
                        <td class="text-end">${{ number_format($order->total, 2) }}</td>
                        <td>
                            @php
                                $statusClass = match($order->status) {
                                    'completed' => 'bg-success-subtle text-success',
                                    'pending' => 'bg-warning-subtle text-warning',
                                    'cancelled' => 'bg-danger-subtle text-danger',
                                    default => 'bg-secondary-subtle text-secondary',
                                };
                            @endphp
                            <span class="badge badge-status {{ $statusClass }}">{{ $order->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No orders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2">
                    <h2 class="h6 mb-0">Recent Customers</h2>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.users.index') }}">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Latest Order</th>
                                <th class="text-end">Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($recentCustomers as $customer)
                            <tr>
                                <td class="fw-medium">{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>
                                    @if ($customer->latest_order_at)
                                        {{ \Carbon\Carbon::parse($customer->latest_order_at)->format('Y-m-d H:i') }}
                                    @else
                                        <span class="text-muted">No orders</span>
                                    @endif
                                </td>
                                <td class="text-end fw-medium">
                                    {{ isset($customer->total_spent) && $customer->total_spent !== null ? '$' . number_format((float) $customer->total_spent, 2) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No customers yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2">
                    <h2 class="h6 mb-0">System Health</h2>
                    <span class="badge bg-{{ (($systemHealth['db_connected'] ?? false) && ($systemHealth['cache_ok'] ?? false)) ? 'success' : 'danger' }}-subtle text-{{ (($systemHealth['db_connected'] ?? false) && ($systemHealth['cache_ok'] ?? false)) ? 'success' : 'danger' }}">
                        {{ (($systemHealth['db_connected'] ?? false) && ($systemHealth['cache_ok'] ?? false)) ? 'Healthy' : 'Issues detected' }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">PHP Version</td>
                                <td class="fw-medium">{{ $systemHealth['php_version'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Laravel Version</td>
                                <td class="fw-medium">{{ $systemHealth['laravel_version'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Environment</td>
                                <td class="fw-medium">{{ $systemHealth['app_env'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Debug Mode</td>
                                <td class="fw-medium">{{ $systemHealth['debug'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Database</td>
                                <td class="fw-medium">
                                    {{ $systemHealth['database_connection'] ?? '—' }}
                                    <span class="badge bg-{{ ($systemHealth['db_connected'] ?? false) ? 'success' : 'danger' }}-subtle text-{{ ($systemHealth['db_connected'] ?? false) ? 'success' : 'danger' }}">
                                        {{ ($systemHealth['db_connected'] ?? false) ? 'Connected' : 'Disconnected' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cache Driver</td>
                                <td class="fw-medium">
                                    {{ $systemHealth['cache_driver'] ?? '—' }}
                                    <span class="badge bg-{{ ($systemHealth['cache_ok'] ?? false) ? 'success' : 'danger' }}-subtle text-{{ ($systemHealth['cache_ok'] ?? false) ? 'success' : 'danger' }}">
                                        {{ ($systemHealth['cache_ok'] ?? false) ? 'Working' : 'Failed' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Queue Connection</td>
                                <td class="fw-medium">{{ $systemHealth['queue_connection'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Storage Free Space</td>
                                <td class="fw-medium">{{ $systemHealth['storage_free_mb'] !== null ? $systemHealth['storage_free_mb'] . ' MB' : 'Unavailable' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="table-card">
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between gap-2">
                    <h2 class="h6 mb-0">Recent Activity</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Who</th>
                                <th>Activity</th>
                                <th>Subject</th>
                                <th>When</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td class="fw-medium">{{ $activity->user?->name ?? 'System' }}</td>
                                <td>{{ $activity->description }}</td>
                                <td>
                                    {{ $activity->subject_type ? class_basename($activity->subject_type) : '—' }}
                                    @if ($activity->subject_id)
                                        <span class="text-muted">#{{ $activity->subject_id }}</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $activity->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No activity yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Theme-aware Chart.js palettes (re-styled when the admin toggles dark mode).
        const palettes = {
            light: {
                text: '#57534E',
                grid: 'rgba(28, 25, 23, .08)',
                doughnut: ['#D97706', '#2F8F5B', '#DC4C3E', '#A8A29E', '#1C1917', '#8B6F47'],
                lineBorder: '#1C1917',
                pointBg: '#ffffff',
            },
            dark: {
                text: '#B8B1A6',
                grid: 'rgba(237, 230, 218, .1)',
                doughnut: ['#F0B26B', '#3DA46C', '#E06A5C', '#8A857F', '#EDE6DA', '#C9A96A'],
                lineBorder: '#F5EFE6',
                pointBg: '#1C1917',
            },
        };
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const charts = [];

        function styleCharts(dark) {
            const p = palettes[dark ? 'dark' : 'light'];
            Chart.defaults.color = p.text;
            for (const c of charts) {
                if (c.config.type === 'doughnut') {
                    c.data.datasets[0].backgroundColor = p.doughnut;
                    c.options.plugins.legend.labels.color = p.text;
                } else if (c.config.type === 'bar' || c.config.type === 'line') {
                    c.options.scales.x.ticks.color = p.text;
                    c.options.scales.y.ticks.color = p.text;
                    c.options.scales.x.grid.color = p.grid;
                    c.options.scales.y.grid.color = p.grid;
                    if (c.config.type === 'line') {
                        c.data.datasets[0].borderColor = p.lineBorder;
                        c.data.datasets[0].pointBackgroundColor = p.pointBg;
                    }
                }
                c.update();
            }
        }

        const statusData = @json($statusBreakdown);
        const labels = Object.keys(statusData || {});
        const values = Object.values(statusData || {});

        const statusCtx = document.getElementById('orderStatusChart');
        if (statusCtx) {
            charts.push(new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: palettes[isDark ? 'dark' : 'light'].doughnut,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                    },
                },
            }));
        }

        const weeklyData = @json($weeklyOrders);
        const weeklyLabels = Object.keys(weeklyData || {}).map(date => date.slice(5));
        const weeklyValues = Object.values(weeklyData || {});

        const weeklyCtx = document.getElementById('weeklyOrdersChart');
        if (weeklyCtx) {
            charts.push(new Chart(weeklyCtx, {
                type: 'bar',
                data: {
                    labels: weeklyLabels,
                    datasets: [{
                        label: 'Orders',
                        data: weeklyValues,
                        backgroundColor: '#D97706',
                        borderRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 },
                        },
                    },
                },
            }));
        }

        const revenueData = @json($revenueWeekly);
        const revenueLabels = Object.keys(revenueData || {}).map(date => date.slice(5));
        const revenueValues = Object.values(revenueData || {});

        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            charts.push(new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: revenueLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: revenueValues,
                        borderColor: palettes[isDark ? 'dark' : 'light'].lineBorder,
                        backgroundColor: 'rgba(180, 83, 9, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: palettes[isDark ? 'dark' : 'light'].pointBg,
                        pointBorderColor: '#B45309',
                        pointBorderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return '$' + value;
                                },
                            },
                        },
                    },
                },
            }));
        }

        styleCharts(isDark);
        window.addEventListener('zenora-theme-change', (e) => styleCharts(e.detail === 'dark'));
    });
</script>
@endpush

