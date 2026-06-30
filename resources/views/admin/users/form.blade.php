@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('content')
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Edit User</h1>

    <div class="card" style="padding: 1rem; max-width: 640px;">
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

        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input-field" style="width: 100%;" required />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input-field" style="width: 100%;" required />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">New Password (leave blank to keep)</label>
                <input type="password" name="password" class="input-field" style="width: 100%;" />
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Confirm Password</label>
                <input type="password" name="password_confirmation" class="input-field" style="width: 100%;" />
            </div>

            <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} />
                <label for="is_admin" style="font-size: 0.875rem; font-weight: 500; color: #334155;">Is Admin</label>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #334155; margin-bottom: 0.35rem;">Role</label>
                <select name="role" class="input-field" style="width: 100%;" {{ !old('is_admin', $user->is_admin) ? 'disabled' : '' }}>
                    <option value="">Select role</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <a href="{{ route('admin.users.index') }}" class="btn" style="background: #e2e8f0; color: #0f172a;">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
@endsection
