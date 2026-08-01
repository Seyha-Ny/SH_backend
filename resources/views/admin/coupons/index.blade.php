@extends('admin.layouts.app')

@section('title', 'Coupons')

@section('content')
    <div class="page-header">
        <h1 class="h3 mb-0">Coupons</h1>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Coupon
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Min Order</th>
                        <th>Active</th>
                        <th>Usage</th>
                        <th>Starts</th>
                        <th>Ends</th>
                        <th style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($coupons as $coupon)
                    <tr>
                        <td class="fw-medium">{{ $coupon->code }}</td>
                        <td>{{ ucfirst($coupon->type) }}</td>
                        <td>{{ $coupon->type === 'percentage' ? $coupon->value . '%' : '$' . number_format((float) $coupon->value, 2) }}</td>
                        <td>{{ $coupon->min_order_amount ? '$' . number_format((float) $coupon->min_order_amount, 2) : '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $coupon->active ? 'success' : 'secondary' }}-subtle text-{{ $coupon->active ? 'success' : 'secondary' }}">
                                {{ $coupon->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $coupon->used_count }} / {{ $coupon->max_uses ?: '∞' }}</td>
                        <td>{{ $coupon->starts_at?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $coupon->ends_at?->format('Y-m-d') ?? '—' }}</td>
                        <td style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-primary" style="width: auto;">Edit</a>
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirmDestroy('Delete coupon `{{ $coupon->code }}`? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="confirm_code" value="{{ $coupon->code }}">
                                <button type="submit" class="btn btn-danger" style="width: auto;">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">No coupons found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 1rem;">
        {{ $coupons->links() }}
    </div>
@endsection
