@extends('admin.layouts.app')

@section('title', 'Shipping Methods')

@section('content')
    <div class="page-header">
        <h1 class="h3 mb-0">Shipping Methods</h1>
        <a href="{{ route('admin.shipping-methods.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Method
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
                        <th>Name</th>
                        <th>Courier</th>
                        <th>Code</th>
                        <th>Fee</th>
                        <th>Est. Delivery</th>
                        <th>Active</th>
                        <th style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($methods as $method)
                    <tr>
                        <td class="fw-medium">{{ $method->name }}</td>
                        <td>{{ $method->courier ?: '—' }}</td>
                        <td><code>{{ $method->code }}</code></td>
                        <td>${{ number_format((float) $method->fee, 2) }}</td>
                        <td>
                            @if ($method->estimated_days_min && $method->estimated_days_max)
                                {{ $method->estimated_days_min }}–{{ $method->estimated_days_max }} days
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $method->active ? 'success' : 'secondary' }}-subtle text-{{ $method->active ? 'success' : 'secondary' }}">
                                {{ $method->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('admin.shipping-methods.edit', $method) }}" class="btn btn-primary" style="width: auto;">Edit</a>
                            <form method="POST" action="{{ route('admin.shipping-methods.destroy', $method) }}" onsubmit="return confirmDestroy('Delete shipping method `{{ $method->name }}`? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="confirm_code" value="{{ $method->code }}">
                                <button type="submit" class="btn btn-danger" style="width: auto;">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No shipping methods found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 1rem;">
        {{ $methods->links() }}
    </div>
@endsection
