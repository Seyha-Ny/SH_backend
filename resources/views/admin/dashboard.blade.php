@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Dashboard</h1>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div class="card" style="padding: 1.25rem;">
            <div style="color: var(--muted); font-size: 0.875rem;">Orders</div>
            <div style="font-size: 1.75rem; font-weight: 700;">{{ $ordersCount }}</div>
        </div>
        <div class="card" style="padding: 1.25rem;">
            <div style="color: var(--muted); font-size: 0.875rem;">Products</div>
            <div style="font-size: 1.75rem; font-weight: 700;">{{ $productsCount }}</div>
        </div>
        <div class="card" style="padding: 1.25rem;">
            <div style="color: var(--muted); font-size: 0.875rem;">Users</div>
            <div style="font-size: 1.75rem; font-weight: 700;">{{ $usersCount }}</div>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="card" style="padding: 1.25rem; text-decoration: none; color: inherit;">
            <div style="color: var(--muted); font-size: 0.875rem;">Pending Reviews</div>
            <div style="font-size: 1.75rem; font-weight: 700;">{{ $pendingReviews }}</div>
        </a>
        <a href="{{ url('/admin/orders?status=cancellation_requested') }}" class="card" style="padding: 1.25rem; text-decoration: none; color: inherit;">
            <div style="color: var(--muted); font-size: 0.875rem;">Pending Cancellations</div>
            <div style="font-size: 1.75rem; font-weight: 700;">{{ $pendingCancellations }}</div>
        </a>
        <a href="{{ url('/admin/orders?status=return_requested') }}" class="card" style="padding: 1.25rem; text-decoration: none; color: inherit;">
            <div style="color: var(--muted); font-size: 0.875rem;">Pending Returns</div>
            <div style="font-size: 1.75rem; font-weight: 700;">{{ $pendingReturns }}</div>
        </a>
        <div class="card" style="padding: 1.25rem;">
            <div style="color: var(--muted); font-size: 0.875rem;">Categories</div>
            <div style="font-size: 1.75rem; font-weight: 700;">{{ $categoriesCount }}</div>
        </div>
        <div class="card" style="padding: 1.25rem;">
            <div style="color: var(--muted); font-size: 0.875rem;">Total Sales</div>
            <div style="font-size: 1.75rem; font-weight: 700;">${{ number_format($sales, 2) }}</div>
        </div>
    </div>

    <div class="card" style="padding: 1.25rem;">
        <h2 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Recent Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->user->name ?? 'N/A' }}</td>
                        <td>${{ number_format($order->total, 2) }}</td>
                        <td>
                            <span class="badge {{ $order->status === 'completed' ? 'badge-completed' : 'badge-pending' }}">
                                {{ $order->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align: center;">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
