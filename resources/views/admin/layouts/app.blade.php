<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title>@hasSection('title')@yield('title') - @endifAdmin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --brand: #2c3e50;
            --muted: #6c757d;
        }
        body {
            background: #f4f6f9;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--brand);
            color: #ffffffd9;
            overflow-y: auto;
            z-index: 1030;
            transition: transform .2s ease-in-out;
        }
        .sidebar .brand {
            padding: 1.25rem 1.25rem 1rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: #fff;
            letter-spacing: .2px;
        }
        .sidebar .nav {
            padding: .25rem .75rem;
            align-items: flex-start;
        }
        .sidebar .nav .nav-heading {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .25rem;
            color: #ffffffb3;
            padding: 1rem .75rem .25rem;
            margin-top: .5rem;
        }
        .sidebar .nav a,
        .sidebar .nav .nav-link {
            color: #ffffffb3;
            padding: .5rem .75rem;
            border-radius: .375rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            text-decoration: none;
            font-size: .95rem;
            transition: background .15s ease, color .15s ease;
        }
        .sidebar .nav a i,
        .sidebar .nav .nav-link i {
            font-size: 1.05rem;
        }
        .sidebar .nav a:hover,
        .sidebar .nav .nav-link:hover,
        .sidebar .nav a.active,
        .sidebar .nav .nav-link.active {
            background: #ffffff1a;
            color: #fff;
        }
        .sidebar .logout {
            margin: 1.25rem .75rem .75rem;
        }
        .sidebar .logout button {
            width: 100%;
        }
        .main {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
        }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main { margin-left: 0; }
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: .75rem;
            padding: 1.125rem 1.25rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.03);
            height: 100%;
        }
        .stat-card .label {
            font-size: .825rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .15px;
        }
        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #212529;
        }
        .stat-card .quick-action {
            margin-top: .5rem;
        }
        .stat-card .quick-action a {
            font-size: .8rem;
            text-decoration: none;
            font-weight: 600;
        }
        .table-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: .75rem;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,.03);
        }
        .table-card .table {
            margin: 0;
        }
        .table-card .table thead th {
            background: #fafbfc;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            color: #495057;
            font-size: .825rem;
            text-transform: uppercase;
            letter-spacing: .12px;
        }
        .table-card .table > :not(caption) > * > * {
            padding: .75rem .85rem;
        }
        .badge-status {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .2px;
        }
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1070;
        }
        .pagination {
            margin-bottom: 0;
        }
        .low-stock-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: .75rem;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,.03);
        }
        .low-stock-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .6rem .85rem;
            border-bottom: 1px solid #f1f3f5;
        }
        .low-stock-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    @php
        $currentRoute = request()->route()?->getName() ?? '';
        $adminPrefix = str_starts_with($currentRoute, 'admin.');
    @endphp

    <nav class="sidebar" id="adminSidebar" aria-label="Admin sidebar">
        <div class="brand">Admin Panel</div>

        <div class="nav flex-column">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ ($currentRoute === 'admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="nav-heading">Products</div>
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ ($currentRoute === 'products.index' || $currentRoute === 'products.create' || $currentRoute === 'products.edit') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> All Products
            </a>
            <a href="{{ route('admin.products.create') }}" class="nav-link {{ ($currentRoute === 'products.create') ? 'active' : '' }}">
                <i class="bi bi-plus-lg"></i> Add New
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'reviews')) ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i> Reviews
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'categories')) ? 'active' : '' }}">
                <i class="bi bi-folder2"></i> Categories
            </a>

            <div class="nav-heading">Orders</div>
            <a href="{{ route('admin.orders.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'orders')) ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> All Orders
            </a>

            <div class="nav-heading">Settings</div>
            <a href="{{ route('admin.shipping-methods.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'shipping-methods')) ? 'active' : '' }}">
                <i class="bi bi-truck"></i> Shipping Methods
            </a>

            <div class="nav-heading">Customers</div>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'users')) ? 'active' : '' }}">
                <i class="bi bi-people"></i> Users
            </a>

            <div class="nav-heading">Marketing</div>
            <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'coupons')) ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated"></i> Coupons
            </a>
        </div>

        <div class="logout">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <button class="btn btn-dark d-lg-none position-fixed top-0 start-0 m-3" style="z-index:1040;" onclick="document.getElementById('adminSidebar').classList.toggle('show')" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>

    <main class="main">
        <div class="toast-container" id="globalToastContainer"></div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('adminSidebar');
            if (!sidebar) return;
            document.addEventListener('click', function (e) {
                if (window.matchMedia('(max-width: 991.98px)').matches) {
                    const isClickInside = sidebar.contains(e.target) || e.target.closest('button[aria-label="Toggle menu"]');
                    if (!isClickInside) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        });

        function setLoading(btn, loading) {
            if (!btn) return;
            if (loading) {
                btn.dataset.originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
            } else {
                btn.disabled = false;
                btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
            }
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('globalToastContainer');
            if (!container) return;
            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-bg-${type} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            container.appendChild(toastEl);
            const bsToast = new bootstrap.Toast(toastEl, { delay: 3500 });
            bsToast.show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        function confirmDestroy(message = 'Are you sure you want to delete this? This cannot be undone.') {
            return confirm(message);
        }
    </script>
    @stack('scripts')
</body>
</html>
