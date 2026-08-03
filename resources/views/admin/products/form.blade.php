@extends('admin.layouts.app')

@section('title', $product->exists ? 'Edit Product' : 'New Product')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="h3 mb-0">{{ $product->exists ? 'Edit Product' : 'New Product' }}</h1>
            <div class="page-subtitle">
                @if ($product->exists)
                    #{{ $product->id }} · created {{ $product->created_at?->format('M j, Y') }}
                @else
                    Add a new product to your catalog
                @endif
            </div>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Quick stock adjustment lives OUTSIDE the main form (HTML forbids
         nested <form> elements) — a sibling card shown only when editing. --}}
    @if ($product->exists)
        <div class="table-card section-card mb-4">
            <div class="p-3 border-bottom d-flex align-items-center gap-3">
                <span class="section-icon amber"><i class="bi bi-arrow-repeat"></i></span>
                <div>
                    <h2 class="h6 mb-0">Quick Stock Adjustment</h2>
                    <div class="text-muted small">Add or remove stock without opening the catalog</div>
                </div>
                <span class="ms-auto badge badge-status {{ $product->stock <= 0 ? 'bg-danger-subtle text-danger' : ($product->stock <= 5 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success') }}">
                    {{ $product->stock }} in stock
                </span>
            </div>
            <div class="p-3">
                <form method="POST" action="{{ route('admin.products.stock', $product) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-auto">
                        <label for="stock_delta" class="form-label">Adjustment (+ / −)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-arrow-down-up"></i></span>
                            <input type="number" class="form-control" id="stock_delta" name="stock_delta" placeholder="e.g. 10 or -3" required aria-label="Stock adjustment">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-primary">Adjust Stock</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          enctype="multipart/form-data" class="needs-validation" novalidate>
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="row g-4">
            {{-- ── Left column: details + pricing ── --}}
            <div class="col-xl-8">
                {{-- Basic details --}}
                <div class="table-card section-card mb-4">
                    <div class="p-3 border-bottom d-flex align-items-center gap-3">
                        <span class="section-icon"><i class="bi bi-box-seam"></i></span>
                        <div>
                            <h2 class="h6 mb-0">Product Details</h2>
                            <div class="text-muted small">Basic information shoppers will see</div>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required placeholder="e.g. Premium Notebook A">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" placeholder="e.g. ZNR-A-001">
                                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-hint">Optional stock-keeping unit for your records.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                        <option value="">Select category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-0">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" placeholder="Describe the product, its materials, and what makes it special…">{{ old('description', $product->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pricing & inventory --}}
                <div class="table-card section-card mb-4">
                    <div class="p-3 border-bottom d-flex align-items-center gap-3">
                        <span class="section-icon gold"><i class="bi bi-tags"></i></span>
                        <div>
                            <h2 class="h6 mb-0">Pricing & Inventory</h2>
                            <div class="text-muted small">Price, stock level, and visibility</div>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" required placeholder="0.00">
                                    </div>
                                    @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-box"></i></span>
                                        <input type="number" min="0" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" required placeholder="0">
                                    </div>
                                    @error('stock')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Active — visible on storefront</option>
                                        <option value="inactive" @selected(old('status', $product->status ?? 'active') === 'inactive')>Inactive — hidden</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Right column: media + publishing ── --}}
            <div class="col-xl-4">
                <div class="form-sidebar">
                    {{-- Main image --}}
                    <div class="table-card section-card mb-4">
                        <div class="p-3 border-bottom d-flex align-items-center gap-3">
                            <span class="section-icon green"><i class="bi bi-image"></i></span>
                            <div>
                                <h2 class="h6 mb-0">Main Image</h2>
                                <div class="text-muted small">Cover photo for the catalog</div>
                            </div>
                        </div>
                        <div class="p-3">
                            <div class="img-preview p-2 mb-3">
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-preview-thumb" id="imagePreview">
                                @else
                                    <div class="d-flex flex-column align-items-center justify-content-center text-muted" id="imagePreviewPlaceholder" style="height: 150px;">
                                        <i class="bi bi-image fs-3 mb-2"></i>
                                        <span class="small">No image yet</span>
                                    </div>
                                    <img src="" alt="Product preview" class="img-preview-thumb d-none" id="imagePreview">
                                @endif
                            </div>
                            <label for="image" class="btn btn-outline-primary w-100">
                                <i class="bi bi-upload me-1"></i> Upload Image
                            </label>
                            {{-- visually-hidden (not d-none) keeps the input in the tab order for keyboard users --}}
                            <input type="file" class="visually-hidden @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                            @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-hint">JPG, PNG, WebP or SVG — up to 4&nbsp;MB.</div>
                        </div>
                    </div>

                    {{-- Gallery --}}
                    <div class="table-card section-card mb-4">
                        <div class="p-3 border-bottom d-flex align-items-center gap-3">
                            <span class="section-icon indigo"><i class="bi bi-collection"></i></span>
                            <div>
                                <h2 class="h6 mb-0">Gallery</h2>
                                <div class="text-muted small">Additional product photos</div>
                            </div>
                        </div>
                        <div class="p-3">
                            @if ($product->exists && $product->productImages->count())
                                <div class="d-flex flex-wrap gap-2 mb-3" id="galleryExisting">
                                    @foreach ($product->productImages as $galleryImage)
                                        <img src="{{ asset('storage/' . $galleryImage->path) }}" alt="Gallery" class="gallery-thumb" loading="lazy">
                                    @endforeach
                                </div>
                            @endif
                            <div class="d-flex flex-wrap gap-2 mb-3 d-none" id="galleryPreviews"></div>
                            <label for="images" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-plus-lg me-1"></i> Add Photos
                            </label>
                            {{-- visually-hidden (not d-none) keeps the input in the tab order for keyboard users --}}
                            <input type="file" class="visually-hidden" id="images" name="images[]" accept="image/*" multiple>
                            <div class="form-hint">Select multiple files — each becomes a gallery image.</div>
                        </div>
                    </div>

                    {{-- Save actions --}}
                    <div class="table-card section-card">
                        <div class="p-3 d-grid gap-2">
                            <button type="submit" class="btn btn-primary save-btn">
                                <i class="bi bi-check-lg me-1"></i>{{ $product->exists ? 'Save Changes' : 'Create Product' }}
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Loading state on save
        const saveBtn = document.querySelector('.save-btn');
        if (saveBtn && saveBtn.closest('form')) {
            saveBtn.closest('form').addEventListener('submit', () => setLoading(saveBtn, true));
        }

        // Live preview for the main image
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const imagePlaceholder = document.getElementById('imagePreviewPlaceholder');
        if (imageInput && imagePreview) {
            let previousUrl = null;
            imageInput.addEventListener('change', () => {
                const file = imageInput.files[0];
                if (!file) return;
                if (previousUrl) URL.revokeObjectURL(previousUrl);
                previousUrl = URL.createObjectURL(file);
                imagePreview.src = previousUrl;
                imagePreview.classList.remove('d-none');
                if (imagePlaceholder) imagePlaceholder.classList.add('d-none');
            });
        }

        // Live thumbnails for gallery uploads
        const imagesInput = document.getElementById('images');
        const galleryPreviews = document.getElementById('galleryPreviews');
        if (imagesInput && galleryPreviews) {
            imagesInput.addEventListener('change', () => {
                galleryPreviews.innerHTML = '';
                for (const file of imagesInput.files) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.alt = 'New gallery image';
                    img.className = 'gallery-thumb';
                    galleryPreviews.appendChild(img);
                }
                galleryPreviews.classList.toggle('d-none', imagesInput.files.length === 0);
            });
        }
    });
</script>
@endpush
