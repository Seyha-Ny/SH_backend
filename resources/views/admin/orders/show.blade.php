@extends('admin.layouts.app')

@section('title', 'Order #' . $order->id)

@section('content')
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Back</a>
            <h1 class="h3 mb-0">Order #{{ $order->id }}</h1>
        </div>
        <div>
            @php
                $statusClass = match($order->status) {
                    'completed' => 'bg-success-subtle text-success',
                    'pending' => 'bg-warning-subtle text-warning',
                    'cancelled' => 'bg-danger-subtle text-danger',
                    default => 'bg-secondary-subtle text-secondary',
                };
            @endphp
            <span class="badge badge-status {{ $statusClass }} fs-6">{{ $order->status }}</span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Order Items</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if ($order->items?->count())
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="fw-medium">{{ $item->product?->name ?? ('Product #' . $item->product_id) }}</td>
                                    <td class="text-end">${{ number_format($item->price, 2) }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="4" class="text-center text-muted py-3">No items.</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Customer</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> {{ $order->user?->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $order->user?->email ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d H:i') }}</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header fw-semibold">Order Total</div>
                <div class="card-body">
                    <p class="h4 mb-0">${{ number_format($order->total, 2) }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">Update Status</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-2">
                            <select class="form-select" name="status">
                                <option value="pending" @selected($order->status === 'pending')>Pending</option>
                                <option value="completed" @selected($order->status === 'completed')>Completed</option>
                                <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="tracking_number" placeholder="Tracking number" value="{{ old('tracking_number', $order->tracking_number) }}">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 save-btn">Update Status</button>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header fw-semibold">Email Customer</div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form method="POST" action="{{ route('admin.orders.mail', $order) }}">
                        @csrf
                        <div class="mb-2">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required value="{{ old('subject', 'Update on your order #' . $order->id) }}">
                        </div>
                        <div class="mb-2">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required>{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 send-mail-btn">Send Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const setupForm = (selector) => {
            const btn = document.querySelector(selector);
            if (!btn) return;
            const form = btn.closest('form');
            if (!form) return;
            form.addEventListener('submit', () => setLoading(btn, true));
        };
        setupForm('.save-btn');
        setupForm('.send-mail-btn');
    });
</script>
@endpush
