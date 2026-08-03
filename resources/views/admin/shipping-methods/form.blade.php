@extends('admin.layouts.app')

@section('title', $method->exists ? 'Edit Shipping Method' : 'Create Shipping Method')

@section('content')
    <div class="page-header">
        <h1 class="h3 mb-0">{{ $method->exists ? 'Edit Shipping Method' : 'Create Shipping Method' }}</h1>
        <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card" style="max-width: 720px;">
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

            @if (session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ $method->exists ? route('admin.shipping-methods.update', $method) : route('admin.shipping-methods.store') }}" onsubmit="setLoading(this.querySelector('button[type=submit]'), true)">
                @csrf
                @if ($method->exists)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $method->name) }}" class="form-control" required maxlength="120" />
                </div>

                <div class="mb-3">
                    <label for="courier" class="form-label">Courier</label>
                    <input type="text" id="courier" name="courier" value="{{ old('courier', $method->courier) }}" class="form-control" maxlength="120" placeholder="e.g. DHL, FedEx" />
                </div>

                <div class="mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" id="code" name="code" value="{{ old('code', $method->code) }}" class="form-control text-uppercase" required maxlength="50" />
                </div>

                <div class="mb-3">
                    <label for="fee" class="form-label">Fee</label>
                    <input type="number" step="0.01" min="0" id="fee" name="fee" value="{{ old('fee', $method->fee) }}" class="form-control" required />
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control" maxlength="1000">{{ old('description', $method->description) }}</textarea>
                </div>

                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" {{ old('active', $method->active) ? 'checked' : '' }} />
                    <label class="form-check-label" for="active">Active</label>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="estimated_days_min" class="form-label">Estimated Delivery Min Days</label>
                        <input type="number" min="1" id="estimated_days_min" name="estimated_days_min" value="{{ old('estimated_days_min', $method->estimated_days_min) }}" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label for="estimated_days_max" class="form-label">Estimated Delivery Max Days</label>
                        <input type="number" min="1" id="estimated_days_max" name="estimated_days_max" value="{{ old('estimated_days_max', $method->estimated_days_max) }}" class="form-control" />
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
