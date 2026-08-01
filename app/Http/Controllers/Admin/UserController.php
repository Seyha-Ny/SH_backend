<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user->loadCount('orders'),
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['boolean'],
            'role' => ['nullable', 'string', 'in:admin,super_admin'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_admin = (bool) ($validated['is_admin'] ?? false);

        if (!empty($validated['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        if ($user->is_admin && !empty($validated['role'])) {
            $user->role = $validated['role'];
        } elseif ($user->is_admin) {
            $user->role = $user->role ?: 'admin';
        }

        $user->save();

        $this->logActivity('updated user: ' . $user->name, $user, ['email' => $user->email, 'is_admin' => (bool) $user->is_admin, 'role' => $user->role]);

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'confirm' => ['required', 'accepted'],
        ]);

        if ($user->id === \Illuminate\Support\Facades\Auth::id()) {
            return back()->withErrors(['confirm' => 'You cannot delete your own account from here.']);
        }

        $user->delete();

        $this->logActivity('deleted user: ' . $user->name, $user, ['name' => $user->name, 'email' => $user->email, 'id' => $user->getKey()]);

        return redirect()->route('admin.users.index')->with('status', 'User deleted successfully.');
    }
}
