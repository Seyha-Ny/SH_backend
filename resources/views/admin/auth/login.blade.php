<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(160deg, #f4f6f9 0%, #eef1f5 100%);
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: 1px solid #e9ecef;
            border-radius: .75rem;
            background: #fff;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,.05), 0 1px 2px rgba(0,0,0,.03);
        }
        .brand {
            font-weight: 700;
            letter-spacing: .2px;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="brand fs-3">Admin Panel</div>
            <p class="text-muted small mb-0">Sign in to continue</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label small fw-medium">Email address</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin@ecommerce.com">
            </div>
            <div class="mb-4">
                <label for="password" class="form-label small fw-medium">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Toggle password">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                Sign In
            </button>
        </form>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        const toggleIcon = document.getElementById('toggleIcon');
        toggleButton.addEventListener('click', function() {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.classList.toggle('bi-eye', !isPassword);
            toggleIcon.classList.toggle('bi-eye-slash', isPassword);
        });
    </script>
</body>
</html>
