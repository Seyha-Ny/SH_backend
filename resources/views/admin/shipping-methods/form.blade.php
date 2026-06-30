@extends('admin.layouts.app')

@section('title', $method->exists ? 'Edit Shipping Method' : 'Create Shipping Method')

@section('content')
    <h1 class="h4 mb-3">{{ $method->exists ? 'Edit Shipping Method' : 'Create Shipping Method' }}</h1>

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

        <form method="POST" action="{{ $method->exists ? route('admin.shipping-methods.update', $method) : route('admin.shipping-methods.store') }}" onsubmit="setLoading(this.querySelector('button[type=submit]'), true)">
            @csrf
            @if ($method->exists)
                @method('PUT')
            @endif

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Name</label>
                <input type="text" name="name" value="{{ old('name', $method->name) }}" class="input-field" style="width: 100%;" required maxlength="120" />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Courier</label>
                <input type="text" name="courier" value="{{ old('courier', $method->courier) }}" class="input-field" style="width: 100%;" maxlength="120" placeholder="e.g. DHL, FedEx" />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Code</label>
                <input type="text" name="code" value="{{ old('code', $method->code) }}" class="input-field" style="width: 100%; text-transform: uppercase;" required maxlength="50" />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Fee</label>
                <input type="number" step="0.01" min="0" name="fee" value="{{ old('fee', $method->fee) }}" class="input-field" style="width: 100%;" required />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Description</label>
                <textarea name="description" rows="3" class="input-field" style="width: 100%;" maxlength="1000">{{ old('description', $method->description) }}</textarea>
            </div>

            <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="active" id="active" value="1" {{ old('active', $method->active) ? 'checked' : '' }} />
                <label for="active" style="font-size: 0.875rem; font-weight: 500; color: #334155;">Active</label>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Estimated Delivery Min Days</label>
                <input type="number" min="1" name="estimated_days_min" value="{{ old('estimated_days_min', $method->estimated_days_min) }}" class="input-field" style="width: 100%;" />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Estimated Delivery Max Days</label>
                <input type="number" min="1" name="estimated_days_max" value="{{ old('estimated_days_max', $method->estimated_days_max) }}" class="input-field" style="width: 100%;" />
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <a href="{{ route('admin.shipping-methods.index') }}" class="btn" style="background: #e2e8f0; color: #0f172a;">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
@endsection
