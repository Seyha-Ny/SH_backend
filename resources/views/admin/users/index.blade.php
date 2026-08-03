@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="h3 mb-0">Users</h1>
            <div class="page-subtitle">Manage customer accounts and staff</div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="table-card">
        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h2 class="h6 mb-0">All Accounts</h2>
                <span class="text-muted small">{{ $users->total() }} user{{ $users->total() === 1 ? '' : 's' }}</span>
            </div>
            <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="Search name or email…" aria-label="Search users" style="min-width: 220px;">
                    @if (request('search'))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" title="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th class="text-center">Orders</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if ($user->avatar)
                                    <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar) }}"
                                         alt="" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <span class="avatar-chip {{ $user->is_admin ? 'amber' : '' }}">
                                        {{ mb_strtoupper(mb_substr(trim($user->name ?: '?'), 0, 1)) }}
                                    </span>
                                @endif
                                <div style="min-width: 0;">
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 280px;" title="{{ $user->email }}">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleBadge = match (true) {
                                    $user->role === 'super_admin' => 'bg-warning-subtle text-warning',
                                    $user->is_admin => 'bg-primary-subtle text-primary',
                                    default => 'bg-secondary-subtle text-secondary',
                                };
                                $roleLabel = match (true) {
                                    $user->role === 'super_admin' => 'Super Admin',
                                    $user->is_admin => 'Admin',
                                    default => $user->role ? ucfirst(str_replace('_', ' ', $user->role)) : 'Customer',
                                };
                            @endphp
                            <span class="badge badge-status {{ $roleBadge }}">{{ $roleLabel }}</span>
                            @if ($user->email_verified_at)
                                <div class="small text-success mt-1"><i class="bi bi-patch-check-fill"></i> Verified</div>
                            @else
                                <div class="small text-muted mt-1"><i class="bi bi-dash-circle"></i> Unverified</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-status bg-body-tertiary text-secondary">{{ $user->orders_count }}</span>
                        </td>
                        <td class="text-muted small text-nowrap" title="{{ $user->created_at->format('F j, Y g:i A') }}">
                            {{ $user->created_at->format('M j, Y') }}
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary" title="Edit user">
                                <i class="bi bi-pencil"></i><span class="d-none d-lg-inline ms-1">Edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" name="confirm" value="1" class="btn btn-sm btn-outline-danger" title="Delete user">
                                    <i class="bi bi-trash"></i><span class="d-none d-lg-inline ms-1">Delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="bi bi-people fs-2 text-muted d-block mb-2"></i>
                            @if (request('search'))
                                No users match “{{ request('search') }}”.
                            @else
                                No users found.
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($users, 'links'))
            <div class="p-3 border-top">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
