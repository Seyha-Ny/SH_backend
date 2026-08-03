@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="h3 mb-0">Products</h1>
            <div class="page-subtitle">Manage your product catalog</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.export', request()->query()) }}" class="btn btn-outline-primary">
                <i class="bi bi-download"></i> Export CSV
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> New Product
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ── Catalog summary (KPI row) ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-box-seam"></i></div>
                <div class="label">Total Products</div>
                <div class="value">{{ $stats['total'] }}</div>
                <div class="quick-action"><a href="{{ route('admin.products.create') }}">+ Add Product</a></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check2-circle"></i></div>
                <div class="label">Active</div>
                <div class="value">{{ $stats['active'] }}</div>
                <div class="quick-action"><a href="{{ route('admin.products.index', ['status' => 'active']) }}">View Active</a></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon gold"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="label">Low Stock</div>
                <div class="value">{{ $stats['low_stock'] }}</div>
                <div class="quick-action"><a href="{{ route('admin.products.index', ['stock' => 'low']) }}">View Low Stock</a></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon indigo"><i class="bi bi-folder2-open"></i></div>
                <div class="label">Categories</div>
                <div class="value">{{ $stats['categories'] }}</div>
                <div class="quick-action"><a href="{{ route('admin.categories.index') }}">Manage Categories</a></div>
            </div>
        </div>
    </div>

    {{-- ── Filters + table ── --}}
    <div class="table-card">
        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h2 class="h6 mb-0">All Products</h2>
                <span class="text-muted small">{{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }}</span>
            </div>
            <form method="GET" action="{{ route('admin.products.index') }}" class="d-flex flex-wrap align-items-end gap-2">
                <div class="input-group input-group-sm" style="min-width: 220px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="Search name, SKU, slug…" aria-label="Search products">
                    @if (request('search'))
                        <a href="{{ route('admin.products.index', request()->except(['search', 'page'])) }}" class="btn btn-outline-secondary" title="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
                <select name="category_id" class="form-select form-select-sm" aria-label="Filter by category" style="min-width: 150px;">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select form-select-sm" aria-label="Filter by status" style="min-width: 120px;">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <select name="stock" class="form-select form-select-sm" aria-label="Filter by stock level" style="min-width: 130px;">
                    <option value="">Any Stock</option>
                    <option value="in" @selected(request('stock') === 'in')>In Stock</option>
                    <option value="low" @selected(request('stock') === 'low')>Low Stock</option>
                    <option value="out" @selected(request('stock') === 'out')>Out of Stock</option>
                </select>
                <button class="btn btn-sm btn-primary" type="submit">Filter</button>
                @if (request()->hasAny(['search', 'category_id', 'status', 'stock']))
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.products.index') }}">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">Image</th>
                        <th>Name</th>
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
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                     class="rounded" style="width: 46px; height: 46px; object-fit: cover; border: 1px solid var(--zenora-border);">
                            @else
                                <div class="d-flex align-items-center justify-content-center"
                                     style="width: 46px; height: 46px; border-radius: .5rem; background: var(--zenora-surface-sunken); border: 1px solid var(--zenora-border); color: var(--zenora-ink-soft);">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            <div class="text-muted small text-nowrap">
                                @if ($product->sku)
                                    <span class="text-uppercase" style="font-size: .7rem; letter-spacing: .05em;">{{ $product->sku }}</span>
                                @else
                                    #{{ $product->id }}
                                @endif
                            </div>
                        </td>
                        <td>
                            @if ($product->category)
                                <span class="badge bg-body-tertiary text-secondary">{{ $product->category->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold text-nowrap">${{ number_format($product->price, 2) }}</td>
                        <td class="text-end text-nowrap">
                            @php
                                $stockClass = match (true) {
                                    $product->stock <= 0 => 'bg-danger-subtle text-danger',
                                    $product->stock <= 5 => 'bg-warning-subtle text-warning',
                                    default => 'bg-success-subtle text-success',
                                };
                            @endphp
                            <span class="badge badge-status {{ $stockClass }}">
                                <i class="bi bi-{{ $product->stock <= 0 ? 'x-circle' : 'box' }} me-1"></i>{{ $product->stock }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusClass = $product->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';
                            @endphp
                            <span class="badge badge-status {{ $statusClass }}">{{ $product->status ?? 'inactive' }}</span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary" title="Edit product">
                                <i class="bi bi-pencil"></i><span class="d-none d-lg-inline ms-1">Edit</span>
                            </a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline confirm-destroy">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete product">
                                    <i class="bi bi-trash"></i><span class="d-none d-lg-inline ms-1">Delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-box-seam fs-2 text-muted d-block mb-2"></i>
                            @if (request()->hasAny(['search', 'category_id', 'status', 'stock']))
                                No products match your filters.
                                <a href="{{ route('admin.products.index') }}" class="d-block mt-2">Clear all filters</a>
                            @else
                                No products yet.
                                <a href="{{ route('admin.products.create') }}" class="d-block mt-2">Create your first product</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($products, 'links') && $products->hasPages())
            <div class="p-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="text-muted small">
                    Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }}
                </span>
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form.confirm-destroy').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!confirm('Delete this product? This cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush
