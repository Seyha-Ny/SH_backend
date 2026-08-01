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
        :root {
            --brand: #4f46e5;
            --brand-dark: #4338ca;
            --muted: #64748b;
            --border: #e2e8f0;
            --bg: #f8fafc;
        }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(160deg, #eef2ff 0%, #f8fafc 50%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }
        .login-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,.06), 0 8px 10px -6px rgba(0,0,0,.04);
            padding: 2rem;
        }
        .brand {
            font-weight: 800;
            color: var(--brand);
            letter-spacing: -0.2px;
            text-decoration: none;
        }
        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.875rem;
            margin-bottom: 0.375rem;
        }
        .form-control {
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            color: #0f172a;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .form-control:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
        }
        .input-group-text {
            background: #fff;
            border: 1px solid var(--border);
            border-left: 0;
            color: var(--muted);
            cursor: pointer;
            border-radius: 0 0.5rem 0.5rem 0 !important;
            padding: 0.625rem 0.75rem;
        }
        .form-control.password-input {
            border-radius: 0.5rem 0 0 0.5rem;
        }
        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            background: var(--brand);
            border: none;
            border-radius: 0.5rem;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            transition: background .15s ease, transform .05s ease;
        }
        .btn-primary:hover {
            background: var(--brand-dark);
        }
        .btn-primary:active {
            transform: translateY(1px);
        }
        .btn-primary:disabled {
            opacity: .7;
            cursor: not-allowed;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 0.5rem;
            padding: 0.875rem 1rem;
            font-size: 0.9375rem;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            color: var(--muted);
            text-decoration: none;
            font-weight: 500;
            transition: color .15s ease;
        }
        .back-link:hover {
            color: #0f172a;
        }
        .form-check-input:checked {
            background-color: var(--brand);
            border-color: var(--brand);
        }
        .form-check-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="text-center mb-4">
                <a href="/" class="brand fs-3 text-decoration-none">Admin Panel</a>
                <p class="text-muted small mb-0">Sign in to continue</p>
            </div>

            <div id="loginErrorContainer"></div>

            <form method="POST" action="{{ route('admin.login.post') }}" novalidate id="adminLoginForm">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin@ecommerce.com">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control password-input" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                        <span class="input-group-text" id="togglePassword" role="button" tabindex="0" aria-label="Toggle password visibility">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                        <label class="form-check-label small text-muted" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn-primary" id="loginButton">
                    Sign In
                </button>
            </form>
        </div>

        <div class="text-center mt-3">
            <a href="/" class="back-link">
                <i class="bi bi-arrow-left"></i> Back to store
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('adminLoginForm');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const toggleIcon = document.getElementById('toggleIcon');
            const loginButton = document.getElementById('loginButton');
            const errorContainer = document.getElementById('loginErrorContainer');

            function showFieldError(message) {
                if (!errorContainer) return;
                errorContainer.innerHTML = '<div class="alert-error">' + message + '</div>';
            }

            function togglePasswordVisibility() {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.classList.toggle('bi-eye', !isPassword);
                toggleIcon.classList.toggle('bi-eye-slash', isPassword);
            }

            if (togglePassword) {
                togglePassword.addEventListener('click', togglePasswordVisibility);
                togglePassword.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        togglePasswordVisibility();
                    }
                });
            }

            if (form) {
                form.addEventListener('submit', function () {
                    if (loginButton) {
                        loginButton.disabled = true;
                        loginButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Signing in...';
                    }
                });
            }
        });
    </script>
</body>
</html>
