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
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="fw-medium">{{ $user->name }}</td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            @if ($user->is_admin)
                                <span class="badge badge-status bg-primary-subtle text-primary">Admin</span>
                            @else
                                <span class="badge badge-status bg-secondary-subtle text-secondary">{{ $user->role ? ucfirst(str_replace('_', ' ', $user->role)) : 'Customer' }}</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $user->created_at->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" name="confirm" value="1" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($users, 'links'))
            <div class="p-3 border-top">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
