@extends('admin.layouts.app')

@section('title', 'User #' . $user->id)

@section('content')
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back</a>
            <h1 class="h3 mb-0">User #{{ $user->id }}</h1>
        </div>
        <div>
            @if ($user->is_admin)
                <span class="badge bg-primary-subtle text-primary">Admin</span>
                <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
            @else
                <span class="badge bg-secondary-subtle text-secondary">Customer</span>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Information</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> {{ $user->name }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                    <p class="mb-1"><strong>Joined:</strong> {{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d') }}</p>
                    <p class="mb-0"><strong>Orders:</strong> {{ $user->orders_count }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">Actions</div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">Edit User</a>
                </div>
            </div>
        </div>
    </div>
@endsection
