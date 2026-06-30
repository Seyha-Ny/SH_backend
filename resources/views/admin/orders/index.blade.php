@extends('admin.layouts.app')

@section('title', 'Orders')

@section('content')
    <div class="page-header">
        <h1 class="h3 mb-0">Orders</h1>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-sm-6 col-md-4">
                    <label class="form-label small text-muted">Search</label>
                    <input type="text" class="form-control" name="search" placeholder="Order ID or customer name" value="{{ request('search') }}">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-sm-6 col-md-2">
                    <label class="form-label small text-muted">From</label>
                    <input type="date" class="form-control" name="from" value="{{ request('from') }}">
                </div>
                <div class="col-sm-6 col-md-2">
                    <label class="form-label small text-muted">To</label>
                    <input type="date" class="form-control" name="to" value="{{ request('to') }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
                <div class="col-auto">
                    <a class="btn btn-outline-secondary" href="{{ route('admin.orders.index') }}">Reset</a>
                </div>
                <div class="col-auto">
                    <a class="btn btn-outline-primary" href="{{ route('admin.orders.export', request()->query()) }}">Export CSV</a>
                </div>
            </form>
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
                        <th>Tracking</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="fw-medium">#{{ $order->id }}</td>
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
                        <td>{{ $order->tracking_number ?: '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">View Details</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No orders found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($orders, 'links'))
            <div class="p-3 border-top">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
