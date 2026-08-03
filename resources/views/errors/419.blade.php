<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired</title>
    <style>
        :root {
            --brand: #5b36ed;
            --brand-dark: #4529b8;
            --ink: #1f2430;
            --muted: #6b7280;
            --bg: #f0f4f8;
            --card: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--ink);
            padding: 1.5rem;
        }
        .card {
            background: var(--card);
            border-radius: 18px;
            box-shadow: 0 12px 40px rgba(23, 32, 64, .10);
            max-width: 430px;
            width: 100%;
            padding: 2.75rem 2.25rem;
            text-align: center;
        }
        .icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: #f1edff;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: float 2.6s ease-in-out infinite;
        }
        .icon svg { width: 34px; height: 34px; }
        h1 {
            font-size: 1.45rem;
            font-weight: 700;
            margin: 0 0 .5rem;
            letter-spacing: -.2px;
        }
        p {
            font-size: .95rem;
            color: var(--muted);
            line-height: 1.6;
            margin: 0 0 1.75rem;
        }
        .actions { display: flex; flex-direction: column; gap: .65rem; }
        .btn {
            display: block;
            width: 100%;
            padding: .8rem 1rem;
            border-radius: 10px;
            font-size: .95rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: background .15s ease, transform .1s ease;
        }
        .btn-primary {
            background: var(--brand);
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--brand-dark);
        }
        .btn-ghost {
            background: #f3f4f6;
            color: var(--ink);
        }
        .btn-ghost:hover {
            background: #e9eaee;
        }
        .btn:active { transform: translateY(1px); }
        .code {
            margin-top: 1.5rem;
            font-size: .72rem;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #b0b6c1;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
    </style>
</head>
<body>
    @php $home = rtrim((string) config('app.frontend_url'), '/') ?: '/'; @endphp
    <div class="card">
        <div class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="#5b36ed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <h1>Your session has expired</h1>
        <p>For your security, your sign-in session ended. Please sign in again to continue shopping or managing your store.</p>
        <div class="actions">
            <a class="btn btn-primary" href="{{ $home }}/auth">Sign in again</a>
            <a class="btn btn-ghost" href="{{ $home }}">← Back to store</a>
        </div>
        <div class="code">419 · Page Expired</div>
    </div>
</body>
</html>
