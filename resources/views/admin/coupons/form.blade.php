@extends('admin.layouts.app')

@section('title', $coupon->exists ? 'Edit Coupon' : 'Create Coupon')

@section('content')
    <h1 class="h4 mb-3">{{ $coupon->exists ? 'Edit Coupon' : 'Create Coupon' }}</h1>

    <div class="card" style="padding: 1rem; max-width: 720px;">
        @if ($errors->any())
            <div style="margin-bottom: 1rem; padding: 0.75rem; background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; border-radius: 0.375rem;">
                <ul style="margin: 0; padding-left: 1.2rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div style="margin-bottom: 1rem; padding: 0.75rem; background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; border-radius: 0.375rem;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" onsubmit="setLoading(this.querySelector('button[type=submit]'), true)">
            @csrf
            @if ($coupon->exists)
                @method('PUT')
            @endif

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Code</label>
                <input type="text" name="code" value="{{ old('code', $coupon->code) }}" class="input-field" style="width: 100%; text-transform: uppercase;" required maxlength="50" />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Type</label>
                <select name="type" class="input-field" style="width: 100%;" required>
                    <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Percentage</option>
                    <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Value</label>
                <input type="number" step="0.01" min="0" name="value" value="{{ old('value', $coupon->value) }}" class="input-field" style="width: 100%;" required />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Min Order Amount (optional)</label>
                <input type="number" step="0.01" min="0" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" class="input-field" style="width: 100%;" />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Max Uses (optional, leave blank for unlimited)</label>
                <input type="number" min="1" name="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" class="input-field" style="width: 100%;" />
            </div>

            <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="active" id="active" value="1" {{ old('active', $coupon->active) ? 'checked' : '' }} />
                <label for="active" style="font-size: 0.875rem; font-weight: 500; color: #334155;">Active</label>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Starts At (optional)</label>
                <input type="date" name="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d')) }}" class="input-field" style="width: 100%;" />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Ends At (optional)</label>
                <input type="date" name="ends_at" value="{{ old('ends_at', $coupon->ends_at?->format('Y-m-d')) }}" class="input-field" style="width: 100%;" />
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <a href="{{ route('admin.coupons.index') }}" class="btn" style="background: #e2e8f0; color: #0f172a;">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
@endsection
