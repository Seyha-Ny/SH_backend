@extends('admin.layouts.app')

@section('title', $category->exists ? 'Edit Category' : 'New Category')

@section('content')
    <div class="page-header">
        <h1 class="h3 mb-0">{{ $category->exists ? 'Edit Category' : 'New Category' }}</h1>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card" style="max-width: 760px;">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
                @csrf
                @if ($category->exists)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                    @if ($category->image)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $category->image) }}" alt="" style="max-width: 160px; border-radius: .5rem; border: 1px solid var(--zenora-border);">
                        </div>
                    @endif
                    @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary save-btn">Save</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.currentScript?.closest('form') ?? document.querySelector('form');
        const saveBtn = document.querySelector('.save-btn');
        if (!form || !saveBtn) return;

        form.addEventListener('submit', () => {
            setLoading(saveBtn, true);
        });
    });
</script>
@endpush
