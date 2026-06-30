@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <h1 style="font-size: 1.5rem; font-weight: 700;">Users</h1>
    </div>

    @if (session('status'))
        <div style="margin-bottom: 1rem; padding: 0.75rem; background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; border-radius: 0.375rem;">
            {{ session('status') }}
        </div>
    @endif

    <div class="card" style="padding: 0; overflow: auto;">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th style="width: 240px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                    <td>{{ $user->role ? ucfirst(str_replace('_', ' ', $user->role)) : '-' }}</td>
                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    <td style="display: flex; gap: 0.5rem;">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary" style="width: auto;">Edit</a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="width: auto;">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align: center;">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1rem;">
        {{ $users->links() }}
    </div>
@endsection
