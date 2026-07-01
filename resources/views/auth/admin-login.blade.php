<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root { --primary: #4f46e5; --muted: #64748b; --border: #e2e8f0; }
        body { margin: 0; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border: 1px solid var(--border); border-radius: 0.75rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08); width: 100%; max-width: 420px; padding: 2rem; }
        input { width: 100%; padding: 0.625rem; border: 1px solid var(--border); border-radius: 0.375rem; }
        .btn { width: 100%; padding: 0.625rem; background: var(--primary); color: #fff; border: none; border-radius: 0.375rem; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h1 style="text-align: center; margin-bottom: 1.5rem;">Admin Login</h1>

        @if ($errors->any())
            <div style="margin-bottom: 1rem; padding: 0.75rem; background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; border-radius: 0.375rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" required style="padding-right: 2.5rem;">
                    <button type="button" onclick="togglePassword()" style="position: absolute; right: 0.5rem; top: 0.5rem; background: none; border: none; cursor: pointer; color: var(--muted); font-size: 0.75rem;">Show</button>
                </div>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
        <script>
            function togglePassword() {
                const input = document.getElementById('password');
                const btn = document.querySelector('button[onclick="togglePassword()"]');
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.textContent = 'Hide';
                } else {
                    input.type = 'password';
                    btn.textContent = 'Show';
                }
            }
        </script>
    </div>
</body>
</html>
