@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
    <div class="page-header">
        <h1 class="h3 mb-0">Products</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Product
        </a>
        <a href="{{ route('admin.products.export') }}" class="btn btn-outline-primary">
            Export CSV
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Stock</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: .375rem; border: 1px solid #e9ecef;">
                            @else
                                <div style="width: 48px; height: 48px; background: #e9ecef; border-radius: .375rem;"></div>
                            @endif
                        </td>
                        <td class="fw-medium">{{ $product->name }}</td>
                        <td class="text-muted">{{ $product->sku ?? '—' }}</td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td class="text-end">${{ number_format($product->price, 2) }}</td>
                        <td class="text-end">{{ $product->stock }}</td>
                        <td>
                            @php
                                $status = match($product->status) {
                                    'active' => 'bg-success-subtle text-success',
                                    'inactive' => 'bg-secondary-subtle text-secondary',
                                    default => 'bg-light text-dark',
                                };
                            @endphp
                            <span class="badge badge-status {{ $status }}">{{ $product->status ?? 'inactive' }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline confirm-destroy">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">No products found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($products, 'links'))
            <div class="p-3 border-top">{{ $products->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.confirm-destroy form').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!confirm('Delete this product? This cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush
