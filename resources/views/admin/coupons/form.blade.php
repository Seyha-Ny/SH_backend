@extends('admin.layouts.app')

@section('title', $coupon->exists ? 'Edit Coupon' : 'Create Coupon')

@section('content')
    <div class="page-header">
        <h1 class="h3 mb-0">{{ $coupon->exists ? 'Edit Coupon' : 'Create Coupon' }}</h1>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Back</a>
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

            <form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" onsubmit="setLoading(this.querySelector('button[type=submit]'), true)">
                @csrf
                @if ($coupon->exists)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" id="code" name="code" value="{{ old('code', $coupon->code) }}" class="form-control text-uppercase" required maxlength="50" />
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Percentage</option>
                        <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="value" class="form-label">Value</label>
                    <input type="number" step="0.01" min="0" id="value" name="value" value="{{ old('value', $coupon->value) }}" class="form-control" required />
                </div>

                <div class="mb-3">
                    <label for="min_order_amount" class="form-label">Min Order Amount (optional)</label>
                    <input type="number" step="0.01" min="0" id="min_order_amount" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" class="form-control" />
                </div>

                <div class="mb-3">
                    <label for="max_uses" class="form-label">Max Uses (optional, leave blank for unlimited)</label>
                    <input type="number" min="1" id="max_uses" name="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" class="form-control" />
                </div>

                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="active" name="active" value="1" {{ old('active', $coupon->active) ? 'checked' : '' }} />
                    <label class="form-check-label" for="active">Active</label>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="starts_at" class="form-label">Starts At (optional)</label>
                        <input type="date" id="starts_at" name="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d')) }}" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label for="ends_at" class="form-label">Ends At (optional)</label>
                        <input type="date" id="ends_at" name="ends_at" value="{{ old('ends_at', $coupon->ends_at?->format('Y-m-d')) }}" class="form-control" />
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
