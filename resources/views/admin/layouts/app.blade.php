<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title>@hasSection('title')@yield('title') - @endif Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            /* ── Zenora brand palette ── */
            --zenora-cream: #F5EFE6;
            --zenora-cream-soft: #FBF7F0;
            --zenora-ink: #1C1917;
            --zenora-ink-soft: #57534E;
            --zenora-amber: #B45309;
            --zenora-amber-deep: #92400E;
            --zenora-amber-soft: #F0E2C8;
            --zenora-gold: #8B6F47;
            --zenora-border: #E7DCCB;

            /* ── Bootstrap re-theme ── */
            --bs-body-bg: var(--zenora-cream);
            --bs-body-color: #292524;
            --bs-body-color-rgb: 41, 37, 36;
            --bs-secondary-color: #78716C;
            --bs-secondary-color-rgb: 120, 113, 108;
            --bs-tertiary-color: #A8A29E;
            --bs-border-color: var(--zenora-border);
            --bs-border-color-translucent: rgba(139, 111, 71, .16);
            --bs-primary: var(--zenora-ink);
            --bs-primary-rgb: 28, 25, 23;
            --bs-primary-bg-subtle: var(--zenora-amber-soft);
            --bs-primary-text-emphasis: #6B4518;
            --bs-link-color: #8A5A2B;
            --bs-link-color-rgb: 138, 90, 43;
            --bs-link-hover-color: #6B4518;
            --bs-link-hover-color-rgb: 107, 69, 24;
            --bs-focus-ring-color: rgba(180, 83, 9, .28);
            --bs-font-sans-serif: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            --bs-border-radius: .6rem;
            --bs-border-radius-sm: .45rem;
            --bs-border-radius-lg: .8rem;
            --bs-border-radius-xl: 1rem;
            --bs-card-border-radius: 1rem;
            --bs-card-spacer-x: 1.3rem;
            --bs-card-spacer-y: 1.1rem;
            --bs-btn-border-radius: .6rem;
            --bs-btn-border-radius-sm: .5rem;
            --bs-btn-padding-y: .55rem;
            --bs-btn-padding-x: 1.05rem;

            /* ── Semantic surfaces (re-mapped for dark mode) ── */
            --zenora-surface: #FFFFFF;
            --zenora-surface-sunken: #F6F0E4;
            --zenora-row-hover: #FBF6EC;
            --zenora-input-bg: #FFFFFF;
            --zenora-input-border: #DCCFB6;
            --zenora-progress-track: #EDE3D0;
        }

        body {
            background:
                radial-gradient(1200px 500px at 85% -10%, rgba(240, 226, 200, .55), transparent 60%),
                radial-gradient(900px 420px at -10% 110%, rgba(232, 217, 188, .4), transparent 60%),
                var(--zenora-cream);
            font-family: var(--bs-font-sans-serif);
            min-height: 100vh;
            margin: 0;
        }

        /* ───────────────────────── Sidebar ───────────────────────── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 248px;
            background: linear-gradient(180deg, #1C1917 0%, #27211C 55%, #2E261F 100%);
            color: rgba(255, 255, 255, .85);
            /* The nav is the only scrollable region (it owns overflow-y). */
            overflow: hidden;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, .06);
            transition: transform .25s ease-in-out;
        }
        .sidebar .brand {
            padding: 1.05rem 1.15rem .95rem;
            display: flex;
            align-items: center;
            gap: .7rem;
            font-weight: 800;
            font-size: 1.08rem;
            letter-spacing: .01em;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
        }
        .brand-mark {
            width: 38px;
            height: 38px;
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #fff;
            background: linear-gradient(135deg, var(--zenora-amber), #D97706);
            box-shadow: 0 6px 16px -6px rgba(217, 119, 6, .55);
            flex-shrink: 0;
        }
        .brand-mark svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .sidebar .nav {
            padding: .3rem .85rem .6rem;
            align-items: stretch;
            flex: 1;
            /* Never wrap into a second column (Bootstrap .nav defaults to
               flex-wrap: wrap) — wrapped items overflow past the sidebar
               edge, get clipped mid-word, and draw a horizontal scrollbar.
               The nav scrolls vertically instead, with a slim styled rail. */
            flex-wrap: nowrap;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .15) transparent;
        }
        .sidebar .nav .nav-heading {
            font-size: .64rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .24em;
            color: rgba(255, 255, 255, .4);
            padding: .8rem .85rem .3rem;
            margin-top: .2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar .nav a,
        .sidebar .nav .nav-link {
            position: relative;
            color: rgba(255, 255, 255, .78);
            padding: .5rem .78rem;
            border-radius: .65rem;
            display: flex;
            align-items: center;
            gap: .7rem;
            text-decoration: none;
            font-size: .92rem;
            font-weight: 500;
            border: 1px solid transparent;
            white-space: nowrap;
            transition: background .15s ease, color .15s ease, transform .15s ease;
        }
        .sidebar .nav .nav-label {
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar .nav a i,
        .sidebar .nav .nav-link i {
            font-size: 1rem;
            width: 1.3rem;
            text-align: center;
            opacity: .95;
            flex-shrink: 0;
        }
        .sidebar .nav a:hover,
        .sidebar .nav .nav-link:hover {
            background: rgba(255, 255, 255, .07);
            color: #fff;
            transform: translateX(2px);
        }
        .sidebar .nav a.active,
        .sidebar .nav .nav-link.active {
            background: linear-gradient(90deg, rgba(180, 83, 9, .32), rgba(180, 83, 9, .1));
            color: #fff;
            border-color: rgba(240, 226, 200, .22);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08), 0 6px 18px -8px rgba(0, 0, 0, .6);
        }
        .sidebar .nav a.active::before,
        .sidebar .nav .nav-link.active::before {
            content: '';
            position: absolute;
            left: 1px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 58%;
            border-radius: 999px;
            background: linear-gradient(180deg, #F59E0B, #D97706);
            box-shadow: 0 0 10px rgba(217, 119, 6, .55);
        }
        .admin-identity {
            padding: .8rem 1rem;
            display: flex;
            align-items: center;
            gap: .7rem;
            border-top: 1px solid rgba(255, 255, 255, .07);
        }
        .admin-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--zenora-amber), #D97706);
            color: #fff;
            font-weight: 800;
            font-size: .95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px -4px rgba(217, 119, 6, .5);
            position: relative;
        }
        .admin-avatar .online-dot {
            position: absolute;
            right: 1px;
            bottom: 1px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #34C77B;
            border: 2px solid #2E261F;
        }
        .admin-identity .name {
            font-size: .88rem;
            font-weight: 600;
            color: #fff;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .admin-identity .email {
            font-size: .72rem;
            color: rgba(255, 255, 255, .5);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar .logout {
            padding: .7rem .85rem 1rem;
            border-top: 1px solid rgba(255, 255, 255, .07);
        }

        /* ─────────────── Collapsible sidebar (icon-only rail) ─────────────── */
        .sidebar { transition: width .22s ease, transform .25s ease-in-out; }
        .main { transition: margin-left .22s ease; }

        .sidebar-toggle {
            position: fixed;
            left: 235px;
            top: 1.15rem;
            width: 27px;
            height: 27px;
            border-radius: 50%;
            border: 1px solid var(--zenora-border);
            background: var(--zenora-surface);
            color: var(--zenora-ink-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .78rem;
            line-height: 1;
            box-shadow: 0 4px 14px -4px rgba(28, 25, 23, .3);
            z-index: 1045;
            transition: left .22s ease, color .15s ease, background .15s ease, transform .15s ease;
        }
        .sidebar-toggle:hover {
            color: var(--zenora-amber);
            background: var(--zenora-surface-sunken);
            transform: scale(1.1);
        }
        .sidebar-toggle:active { transform: scale(.95); }

        /* Icon-only rail — desktop only; the mobile drawer always opens full */
        @media (min-width: 992px) {
            [data-sidebar="collapsed"] .sidebar { width: 76px; }
            [data-sidebar="collapsed"] .main { margin-left: 76px; }
            [data-sidebar="collapsed"] .sidebar-toggle { left: 63px; }

            [data-sidebar="collapsed"] .sidebar .brand {
                padding: 1.05rem .5rem .95rem;
                justify-content: center;
                gap: 0;
            }
            [data-sidebar="collapsed"] .sidebar .nav { padding: .4rem .6rem .6rem; }
            [data-sidebar="collapsed"] .sidebar .nav .nav-heading {
                font-size: 0;
                height: 1px;
                padding: 0;
                margin: .55rem .4rem;
                background: rgba(255, 255, 255, .14);
                border-radius: 999px;
                overflow: hidden;
            }
            [data-sidebar="collapsed"] .sidebar .nav a,
            [data-sidebar="collapsed"] .sidebar .nav .nav-link {
                justify-content: center;
                padding: .58rem 0;
                gap: 0;
            }
            [data-sidebar="collapsed"] .sidebar .nav a:hover,
            [data-sidebar="collapsed"] .sidebar .nav .nav-link:hover { transform: none; }

            [data-sidebar="collapsed"] .sidebar .admin-identity {
                padding: .8rem .5rem;
                justify-content: center;
                gap: 0;
            }

            [data-sidebar="collapsed"] .sidebar .logout {
                padding: .7rem .6rem 1rem;
                display: flex;
                flex-direction: column;
                gap: .5rem;
            }
            [data-sidebar="collapsed"] .sidebar .logout .btn { padding: .55rem 0; }
            [data-sidebar="collapsed"] .sidebar .logout .theme-toggle { gap: 0; }

            /* Visually hide labels while keeping them in the a11y tree. */
            [data-sidebar="collapsed"] .sidebar .nav-label,
            [data-sidebar="collapsed"] .sidebar .brand-text,
            [data-sidebar="collapsed"] .sidebar .identity-text,
            [data-sidebar="collapsed"] .sidebar .logout .btn span {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }
        }

        /* ───────────────────────── Mobile topbar ───────────────────────── */
        .topbar-mobile {
            display: none;
            position: sticky;
            top: 0;
            z-index: 1040;
            align-items: center;
            gap: .8rem;
            padding: .75rem 1rem;
            background: rgba(28, 25, 23, .97);
            backdrop-filter: blur(8px);
            color: #fff;
        }
        .topbar-mobile .btn-menu {
            border: 1px solid rgba(255, 255, 255, .18);
            background: rgba(255, 255, 255, .06);
            color: #fff;
            border-radius: .6rem;
            padding: .45rem .7rem;
            line-height: 1;
        }
        .topbar-mobile .btn-menu:hover { background: rgba(255, 255, 255, .14); }
        .topbar-mobile .topbar-brand {
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 1rem;
        }
        .topbar-mobile .topbar-brand i { color: #D97706; }

        /* ───────────────────────── Main area ───────────────────────── */
        .main {
            margin-left: 248px;
            padding: 1.75rem 2rem 3.5rem;
            min-height: 100vh;
        }
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        .page-header h1 {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -.02em;
            color: var(--zenora-ink);
            margin-bottom: 0;
        }
        .page-header .page-subtitle {
            font-size: .88rem;
            color: var(--bs-secondary-color);
            margin-top: .2rem;
        }

        /* ───────────────────────── Cards ───────────────────────── */
        .stat-card,
        .table-card,
        .low-stock-card {
            background: var(--zenora-surface);
            border: 1px solid var(--zenora-border);
            border-radius: 1rem;
            box-shadow: 0 1px 2px rgba(28, 25, 23, .04), 0 10px 30px -16px rgba(28, 25, 23, .16);
        }
        .stat-card {
            padding: 1.25rem 1.35rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: .3rem;
            position: relative;
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 3px;
            background: linear-gradient(90deg, var(--zenora-amber), rgba(217, 119, 6, 0));
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 36px -14px rgba(28, 25, 23, .22);
        }
        .stat-card .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: .8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #fff;
            background: linear-gradient(135deg, #1C1917, #4A443E);
            box-shadow: 0 6px 14px -6px rgba(28, 25, 23, .5);
            margin-bottom: .45rem;
        }
        .stat-card .stat-icon.amber { background: linear-gradient(135deg, #B45309, #D97706); }
        .stat-card .stat-icon.gold { background: linear-gradient(135deg, #8B6F47, #B08D5F); }
        .stat-card .stat-icon.green { background: linear-gradient(135deg, #1F6F43, #2F8F5B); }
        .stat-card .stat-icon.indigo { background: linear-gradient(135deg, #4338CA, #6366F1); }
        .stat-card .label {
            font-size: .78rem;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: .12em;
            font-weight: 700;
        }
        .stat-card .value {
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: -.02em;
            color: var(--zenora-ink);
            line-height: 1.15;
        }
        .stat-card .quick-action { margin-top: .35rem; }
        .stat-card .quick-action a {
            font-size: .82rem;
            font-weight: 700;
            color: var(--zenora-amber);
            text-decoration: none;
        }
        .stat-card .quick-action a:hover { color: var(--zenora-amber-deep); text-decoration: underline; }

        /* ───────────────────── KPI hero & stat hierarchy ───────────────────── */
        .kpi-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.1rem;
            background:
                radial-gradient(620px 280px at 92% -18%, rgba(217, 119, 6, .3), transparent 62%),
                linear-gradient(135deg, #241E18 0%, #1C1917 58%, #2C251F 100%);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 1px 2px rgba(28, 25, 23, .08), 0 20px 46px -20px rgba(28, 25, 23, .5);
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
            padding: 1.4rem 1.5rem 1.25rem;
            min-height: 100%;
        }
        @media (min-width: 1200px) {
            .kpi-hero { flex-direction: row; align-items: stretch; }
        }
        .kpi-hero-main { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
        .kpi-hero-chart { flex: 0 0 46%; min-width: 0; min-height: 128px; position: relative; }
        .kpi-hero-chart canvas { position: absolute; inset: 0; width: 100%; height: 100%; }
        @media (max-width: 1199.98px) { .kpi-hero-chart { min-height: 112px; } }
        .kpi-hero-icon {
            width: 46px; height: 46px; border-radius: .85rem;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.35rem; color: #fff;
            background: linear-gradient(135deg, #B45309, #F59E0B);
            box-shadow: 0 8px 22px -8px rgba(217, 119, 6, .75);
        }
        .kpi-hero-label {
            font-size: .76rem; text-transform: uppercase; letter-spacing: .16em;
            font-weight: 700; color: rgba(255, 255, 255, .6); margin-top: 1rem;
        }
        .kpi-hero-value {
            font-size: 2.45rem; font-weight: 800; letter-spacing: -.03em;
            line-height: 1.08; color: #fff; margin-top: .1rem;
        }
        .kpi-hero-meta { font-size: .82rem; color: rgba(255, 255, 255, .56); margin-top: .35rem; }
        .kpi-hero-link {
            margin-top: auto; padding-top: 1rem;
            font-size: .84rem; font-weight: 700; color: #F0B26B;
            text-decoration: none; display: inline-flex; align-items: center; gap: .35rem;
        }
        .kpi-hero-link:hover { color: #fff; }
        .kpi-hero-link i { transition: transform .15s ease; }
        .kpi-hero-link:hover i { transform: translateX(3px); }

        .stat-card--secondary .value { font-size: 1.55rem; }
        .stat-spark { height: 54px; position: relative; margin-top: .4rem; }
        .stat-spark canvas { position: absolute; inset: 0; }

        /* ────────────── Monthly revenue target (prominent) ────────────── */
        .target-bar {
            position: relative;
            display: flex;
            align-items: center;
            height: 1.6rem;
            border-radius: 999px;
            background: var(--zenora-progress-track);
            box-shadow: inset 0 1px 3px rgba(28, 25, 23, .14);
            overflow: hidden;
            margin-top: .55rem;
        }
        .target-bar-fill {
            position: relative;
            flex-shrink: 0;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #92400E 0%, #D97706 55%, #F59E0B 100%);
            box-shadow: 0 0 16px rgba(217, 119, 6, .45), inset 0 1px 0 rgba(255, 255, 255, .28);
            transition: width .7s cubic-bezier(.22, .61, .36, 1);
            overflow: hidden;
        }
        .target-bar-fill::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(105deg, transparent 32%, rgba(255, 255, 255, .35) 50%, transparent 68%);
            background-size: 240% 100%;
            animation: zenoraShimmer 2.8s linear infinite;
        }
        @keyframes zenoraShimmer {
            0% { background-position: 240% 0; }
            100% { background-position: -40% 0; }
        }
        @media (prefers-reduced-motion: reduce) {
            .target-bar-fill::after { animation: none; }
        }
        .target-bar-label {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: flex-end;
            padding-right: .65rem;
            font-size: .74rem; font-weight: 800; letter-spacing: .01em;
            color: #fff; text-shadow: 0 1px 2px rgba(28, 25, 23, .45);
        }
        .target-bar-label.out {
            position: static; display: inline-flex; align-items: center;
            padding: 0 0 0 .65rem; color: var(--zenora-ink);
            text-shadow: none;
        }

        /* ─────────────────── Charts (tooltip-safe) ─────────────────── */
        .chart-card { position: relative; }
        /* Higher specificity than .table-card's overflow:hidden so hover
           tooltips can never be clipped at the card edge. */
        .table-card.chart-card { overflow: visible; }
        .zenora-chart-tooltip {
            position: absolute; left: 0; top: 0; z-index: 40;
            pointer-events: none;
            max-width: 220px; white-space: nowrap;
            background: #1C1917; color: #F5EFE6;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: .55rem;
            padding: .45rem .7rem;
            font-size: .76rem; line-height: 1.4;
            box-shadow: 0 12px 28px -12px rgba(28, 25, 23, .55);
            opacity: 0;
            transition: opacity .14s ease;
        }
        .zenora-chart-tooltip .tt-title { font-weight: 700; color: #F0B26B; margin-bottom: .1rem; }
        [data-bs-theme="dark"] .zenora-chart-tooltip {
            background: #F5EFE6; color: #1C1917;
            border-color: rgba(28, 25, 23, .14);
            box-shadow: 0 12px 28px -12px rgba(0, 0, 0, .6);
        }
        [data-bs-theme="dark"] .zenora-chart-tooltip .tt-title { color: #92400E; }

        /* Keep the fixed notification bell clear of page headers. */
        @media (min-width: 992px) {
            .page-header { padding-right: 4.25rem; }
        }

        .table-card, .low-stock-card { overflow: hidden; }
        .table-card > .p-3,
        .low-stock-card > .p-3,
        .table-card > div.p-3 {
            background: linear-gradient(180deg, var(--zenora-cream-soft), var(--zenora-surface));
            border-bottom: 1px solid var(--zenora-border);
        }
        .table-card h2.h6, .low-stock-card h2.h6 { font-weight: 800; color: var(--zenora-ink); letter-spacing: -.01em; }

        /* ───────────────────────── Tables ───────────────────────── */
        .table { --bs-table-bg: transparent; }
        .table thead th {
            background: var(--zenora-surface-sunken);
            border-bottom: 1px solid var(--zenora-border);
            font-weight: 700;
            color: #57534E;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .13em;
            padding: .8rem .85rem;
            white-space: nowrap;
        }
        .table > :not(caption) > * > * { padding: .8rem .85rem; }
        .table tbody tr { transition: background .12s ease; }
        .table tbody tr:hover { background: var(--zenora-row-hover); }
        .table td { border-color: rgba(231, 220, 203, .55); vertical-align: middle; }

        .avatar-chip {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .95rem;
            color: #fff;
            flex-shrink: 0;
            background: linear-gradient(135deg, #8B6F47, #B08D5F);
            box-shadow: 0 4px 10px -4px rgba(28, 25, 23, .4);
        }
        .avatar-chip.amber {
            background: linear-gradient(135deg, #B45309, #D97706);
            box-shadow: 0 4px 10px -4px rgba(217, 119, 6, .5);
        }

        /* ───────────────────────── Badges ───────────────────────── */
        .badge { font-weight: 600; letter-spacing: .02em; }
        .badge-status {
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: .7rem;
            padding: .42em .8em;
            border-radius: 999px;
            font-weight: 700;
        }

        /* ───────────────────────── Buttons ───────────────────────── */
        .btn { font-weight: 600; }
        .btn-primary {
            box-shadow: 0 5px 16px -7px rgba(28, 25, 23, .5);
        }
        .btn-primary:hover, .btn-primary:focus { background: #000; border-color: #000; }
        .btn-outline-secondary {
            color: #57534E;
            border-color: #D9CDB8;
        }
        .btn-outline-secondary:hover {
            background: #EFE6D5;
            border-color: #C9B896;
            color: #292524;
        }
        .btn-outline-primary {
            color: #8A5A2B;
            border-color: #D9CDB8;
        }
        .btn-outline-primary:hover {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
            color: #fff;
        }
        .btn-outline-danger { color: #B3261E; border-color: #E5C4C0; }
        .btn-outline-danger:hover { background: #B3261E; border-color: #B3261E; }
        .btn-sm { padding: .38rem .7rem; font-size: .8rem; }

        /* ───────────────────────── Forms ───────────────────────── */
        .section-card .section-icon {
            width: 36px;
            height: 36px;
            border-radius: .7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #fff;
            background: linear-gradient(135deg, var(--zenora-amber), #D97706);
            box-shadow: 0 5px 14px -6px rgba(217, 119, 6, .55);
            flex-shrink: 0;
        }
        .section-card .section-icon.gold { background: linear-gradient(135deg, #8B6F47, #B08D5F); box-shadow: 0 5px 14px -6px rgba(139, 111, 71, .55); }
        .section-card .section-icon.green { background: linear-gradient(135deg, #1F6F43, #2F8F5B); box-shadow: 0 5px 14px -6px rgba(47, 143, 91, .55); }
        .section-card .section-icon.indigo { background: linear-gradient(135deg, #4338CA, #6366F1); box-shadow: 0 5px 14px -6px rgba(99, 102, 241, .55); }
        @media (min-width: 1200px) {
            .form-sidebar { position: sticky; top: 1.4rem; }
        }
        .img-preview {
            border: 1px solid var(--zenora-border);
            border-radius: .8rem;
            background: var(--zenora-surface-sunken);
        }
        .img-preview-thumb {
            width: 100%;
            max-height: 240px;
            object-fit: cover;
            border-radius: .65rem;
            display: block;
        }
        .gallery-thumb {
            width: 68px;
            height: 68px;
            object-fit: cover;
            border-radius: .55rem;
            border: 1px solid var(--zenora-border);
            box-shadow: 0 3px 10px -4px rgba(28, 25, 23, .2);
        }
        .form-hint { font-size: .76rem; color: var(--bs-secondary-color); margin-top: .3rem; }
        /* Form card bodies are direct .p-3 children too, so reset the header
           gradient + border that .table-card > .p-3 would otherwise give them. */
        .table-card.section-card > .p-3:not(.border-bottom) {
            background: var(--zenora-surface);
            border-bottom: 0;
        }

        .form-control, .form-select {
            border-color: var(--zenora-input-border);
            border-radius: .6rem;
            background-color: var(--zenora-input-bg);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--zenora-amber);
            box-shadow: 0 0 0 .22rem rgba(180, 83, 9, .14);
        }
        .form-label { font-weight: 600; font-size: .8rem; color: var(--zenora-ink-soft); }
        .input-group-text { border-color: var(--zenora-input-border); background: var(--zenora-surface-sunken); color: var(--zenora-ink-soft); }

        /* ───────────────────────── Alerts / toasts / progress ───────────────────────── */
        .alert { border-radius: .8rem; border-color: var(--zenora-border); }
        .progress { background: var(--zenora-progress-track); border-radius: 999px; }
        .progress-bar {
            background: linear-gradient(90deg, var(--zenora-amber), #D97706);
            border-radius: 999px;
        }
        .toast { border-radius: .8rem; box-shadow: 0 10px 30px -10px rgba(28, 25, 23, .3); }

        /* ───────────────────────── Pagination / dropdown ───────────────────────── */
        .pagination { --bs-pagination-border-radius: .55rem; --bs-pagination-color: #57534E; }
        .pagination .page-link { border-color: var(--zenora-border); }
        .pagination .page-item.active .page-link {
            background: var(--bs-primary);
            border-color: var(--bs-primary);
            color: #fff;
        }
        .dropdown-menu {
            border-radius: .8rem;
            border-color: var(--zenora-border);
            box-shadow: 0 16px 40px -16px rgba(28, 25, 23, .25);
        }

        /* ───────────────────────── Notification bell ───────────────────────── */
        #adminBell {
            background: var(--zenora-surface-sunken);
            border: 1px solid var(--zenora-border);
            color: var(--zenora-ink);
            box-shadow: 0 4px 14px -8px rgba(28, 25, 23, .35);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        #adminBell:hover { transform: translateY(-2px) scale(1.03); }
        #bellBadge { background: var(--zenora-amber); box-shadow: 0 0 0 2px #fff; }

        /* ───────────────────────── Responsive ───────────────────────── */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 6px 0 40px -10px rgba(0, 0, 0, .5);
            }
            .main { margin-left: 0; padding: 1.25rem 1rem 3rem; }
            .topbar-mobile { display: flex; }
            .stat-card .value { font-size: 1.7rem; }

            .sidebar-toggle { display: none; }
        }
        @media (min-width: 992px) and (max-width: 1199.98px) {
            .sidebar { width: 232px; }
            .main { margin-left: 232px; padding-left: 1.4rem; padding-right: 1.4rem; }
            .brand-mark { width: 34px; height: 34px; font-size: 1rem; }
            .admin-avatar { width: 34px; height: 34px; font-size: .85rem; }
            .sidebar-toggle { left: 219px; }
            [data-sidebar="collapsed"] .sidebar { width: 72px; }
            [data-sidebar="collapsed"] .main { margin-left: 72px; }
            [data-sidebar="collapsed"] .sidebar-toggle { left: 59px; }
        }
        @media (min-width: 992px) {
            .topbar-mobile { display: none !important; }
        }

        /* ── Dark mode: Zenora charcoal variant ── */
        [data-bs-theme="dark"] {
            --zenora-cream: #15110E;
            --zenora-cream-soft: #1C1713;
            --zenora-ink: #F5EFE6;
            --zenora-ink-soft: #B8B1A6;
            --zenora-amber-soft: rgba(180, 83, 9, .2);
            --zenora-border: #38302A;
            --zenora-surface: #211B16;
            --zenora-surface-sunken: #2C241D;
            --zenora-row-hover: rgba(180, 83, 9, .09);
            --zenora-input-bg: #1A1512;
            --zenora-input-border: #4A3D31;
            --zenora-progress-track: #342B23;

            --bs-body-bg: var(--zenora-cream);
            --bs-body-color: #EDE6DA;
            --bs-body-color-rgb: 237, 230, 218;
            --bs-secondary-color: #A8A29E;
            --bs-secondary-color-rgb: 168, 162, 158;
            --bs-tertiary-color: #78716C;
            --bs-primary: var(--zenora-amber);
            --bs-primary-rgb: 180, 83, 9;
            --bs-primary-text-emphasis: #F0B26B;
            --bs-primary-bg-subtle: rgba(180, 83, 9, .18);
            --bs-border-color: var(--zenora-border);
            --bs-link-color: #E0A14B;
            --bs-link-color-rgb: 224, 161, 75;
            --bs-link-hover-color: #F0B26B;
            --bs-link-hover-color-rgb: 240, 178, 107;
            --bs-focus-ring-color: rgba(217, 119, 6, .4);
        }
        [data-bs-theme="dark"] body {
            background:
                radial-gradient(1200px 500px at 85% -10%, rgba(180, 83, 9, .12), transparent 60%),
                radial-gradient(900px 420px at -10% 110%, rgba(139, 111, 71, .08), transparent 60%),
                var(--zenora-cream);
        }
        [data-bs-theme="dark"] .stat-card .stat-icon {
            background: linear-gradient(135deg, #8B6F47, #B08D5F);
        }
        [data-bs-theme="dark"] .btn-primary:hover,
        [data-bs-theme="dark"] .btn-primary:focus {
            background: var(--zenora-amber-deep);
            border-color: var(--zenora-amber-deep);
        }
        [data-bs-theme="dark"] .btn-outline-secondary {
            color: #B8B1A6;
            border-color: #4A3D31;
        }
        [data-bs-theme="dark"] .btn-outline-secondary:hover {
            background: #2C241D;
            border-color: #5A4B3C;
            color: #EDE6DA;
        }
        [data-bs-theme="dark"] .btn-outline-primary {
            color: #E0A14B;
            border-color: #5A4B3C;
        }
        [data-bs-theme="dark"] .btn-outline-danger {
            color: #E79A92;
            border-color: #6E3A34;
        }
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            border-color: var(--zenora-amber);
            box-shadow: 0 0 0 .22rem rgba(217, 119, 6, .18);
        }
        [data-bs-theme="dark"] .pagination {
            --bs-pagination-bg: var(--zenora-surface);
            --bs-pagination-border-color: var(--zenora-border);
            --bs-pagination-hover-bg: var(--zenora-surface-sunken);
            --bs-pagination-hover-border-color: var(--zenora-border);
            --bs-pagination-color: #B8B1A6;
            --bs-pagination-disabled-bg: var(--zenora-surface);
        }
        [data-bs-theme="dark"] .table {
            --bs-table-hover-bg: var(--zenora-row-hover);
            --bs-table-color: #EDE6DA;
        }
        [data-bs-theme="dark"] .toast {
            --bs-toast-bg: #241E19;
            --bs-toast-header-bg: #2C241D;
        }

        .theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
        }
        [data-bs-theme="dark"] .theme-toggle.btn-outline-light {
            border-color: rgba(255, 255, 255, .28);
        }
    </style>
    <script>
        // Apply the saved/system dark-mode preference before first paint (no flash),
        // plus the persisted sidebar collapse state (icon-only rail).
        (function () {
            try {
                var t = localStorage.getItem('zenora-admin-theme');
                if (t !== 'dark' && t !== 'light') {
                    t = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
            try {
                var s = localStorage.getItem('zenora-admin-sidebar');
                document.documentElement.setAttribute('data-sidebar', s === 'collapsed' ? 'collapsed' : 'expanded');
            } catch (e) {
                document.documentElement.setAttribute('data-sidebar', 'expanded');
            }
        })();
    </script>
</head>
<body>
    @php
        $currentRoute = request()->route()?->getName() ?? '';
        $adminPrefix = str_starts_with($currentRoute, 'admin.');
        $admin = Auth::user();
        $adminInitial = strtoupper(mb_substr(trim((string) ($admin?->name ?: 'A')), 0, 1));
    @endphp

    <nav class="sidebar" id="adminSidebar" aria-label="Admin sidebar">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">
                {{-- Same crown mark as the storefront logo (Logo.vue) --}}
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M5 16L3 7l4.5 4L12 4l4.5 7L21 7l-2 9H5z" />
                </svg>
            </span>
            <span class="brand-text">Zenora</span>
        </div>

        <div class="nav flex-column">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ ($currentRoute === 'admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                <i class="bi bi-speedometer2"></i> <span class="nav-label">Dashboard</span>
            </a>

            <div class="nav-heading">Products</div>
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ ($currentRoute === 'products.index' || $currentRoute === 'products.create' || $currentRoute === 'products.edit') ? 'active' : '' }}" title="All Products">
                <i class="bi bi-box-seam"></i> <span class="nav-label">All Products</span>
            </a>
            <a href="{{ route('admin.products.create') }}" class="nav-link {{ ($currentRoute === 'products.create') ? 'active' : '' }}" title="Add New">
                <i class="bi bi-plus-lg"></i> <span class="nav-label">Add New</span>
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'reviews')) ? 'active' : '' }}" title="Reviews">
                <i class="bi bi-chat-dots"></i> <span class="nav-label">Reviews</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'categories')) ? 'active' : '' }}" title="Categories">
                <i class="bi bi-folder2"></i> <span class="nav-label">Categories</span>
            </a>

            <div class="nav-heading">Orders</div>
            <a href="{{ route('admin.orders.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'orders')) ? 'active' : '' }}" title="All Orders">
                <i class="bi bi-receipt"></i> <span class="nav-label">All Orders</span>
            </a>

            <div class="nav-heading">Settings</div>
            <a href="{{ route('admin.shipping-methods.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'shipping-methods')) ? 'active' : '' }}" title="Shipping Methods">
                <i class="bi bi-truck"></i> <span class="nav-label">Shipping Methods</span>
            </a>

            <div class="nav-heading">Customers</div>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'users')) ? 'active' : '' }}" title="Users">
                <i class="bi bi-people"></i> <span class="nav-label">Users</span>
            </a>

            <div class="nav-heading">Marketing</div>
            <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ (str_starts_with($currentRoute, 'coupons')) ? 'active' : '' }}" title="Coupons">
                <i class="bi bi-ticket-perforated"></i> <span class="nav-label">Coupons</span>
            </a>
        </div>

        @if ($admin)
            <div class="admin-identity">
                <span class="admin-avatar">{{ $adminInitial }}<span class="online-dot"></span></span>
                <span class="min-w-0 identity-text">
                    <span class="name d-block">{{ $admin->name }}</span>
                    <span class="email d-block">{{ $admin->email }}</span>
                </span>
            </div>
        @endif

        <div class="logout">
            <button type="button" class="btn btn-outline-light w-100 mb-2 theme-toggle" id="themeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
                <i class="bi bi-moon-stars-fill" id="themeToggleIcon"></i>
                <span id="themeToggleLabel">Dark</span>
            </button>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100" title="Logout">
                    <i class="bi bi-box-arrow-right"></i> <span class="logout-label">Logout</span>
                </button>
            </form>
        </div>
    </nav>

    {{-- Sidebar collapse toggle (desktop icon rail) --}}
    <button type="button" id="sidebarToggle" class="sidebar-toggle" aria-label="Collapse sidebar" aria-expanded="true" title="Collapse sidebar">
        <i class="bi bi-chevron-double-left" id="sidebarToggleIcon"></i>
    </button>

    {{-- Mobile topbar --}}
    <div class="topbar-mobile">
        <button class="btn-menu" type="button" onclick="document.getElementById('adminSidebar').classList.add('show')" aria-label="Toggle menu">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="topbar-brand">
            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 18px; height: 18px;" aria-hidden="true">
                <path d="M5 16L3 7l4.5 4L12 4l4.5 7L21 7l-2 9H5z" />
            </svg>
            Zenora
        </span>
        <div class="ms-auto d-flex align-items-center gap-2">
            <button class="btn-menu" type="button" id="themeToggleMobile" aria-label="Toggle dark mode" title="Toggle dark mode">
                <i class="bi bi-moon-stars-fill" id="themeToggleIconMobile"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-menu position-relative" type="button" id="adminBellMobile" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                </button>
            </div>
        </div>
    </div>

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

    {{-- Admin notification bell (order-placed alerts) --}}
    <div class="position-fixed d-none d-lg-block" style="top: 1rem; right: 1.5rem; z-index: 1050;">
        <div class="dropdown">
            <button
                class="btn btn-light btn-lg rounded-circle shadow-sm position-relative"
                type="button"
                id="adminBell"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Notifications"
            >
                <i class="bi bi-bell"></i>
                <span
                    id="bellBadge"
                    class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger d-none"
                    style="font-size: .6rem; padding: .35em .6em;"
                >0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg" style="width: 360px; max-height: 480px; overflow-y: auto;" aria-labelledby="adminBell">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-body-tertiary">
                    <span class="fw-semibold">Notifications</span>
                    <button class="btn btn-sm btn-link p-0 text-decoration-none" type="button" id="markAllReadBtn">Mark all read</button>
                </div>
                <div id="notifList" class="py-1">
                    <div class="text-center text-muted py-4">Loading…</div>
                </div>
            </div>
        </div>
    </div>

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

        // ---- Dark mode toggle (persisted per browser) ----
        const themeToggle = document.getElementById('themeToggle');
        const themeToggleIcon = document.getElementById('themeToggleIcon');
        const themeToggleLabel = document.getElementById('themeToggleLabel');
        const themeToggleMobile = document.getElementById('themeToggleMobile');
        const themeToggleIconMobile = document.getElementById('themeToggleIconMobile');

        function zenoraCurrentTheme() {
            return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
        }

        function zenoraApplyTheme(theme, persist) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.style.colorScheme = theme;
            const dark = theme === 'dark';
            const icon = dark ? 'bi-sun-fill' : 'bi-moon-stars-fill';
            if (themeToggleIcon) themeToggleIcon.className = 'bi ' + icon;
            if (themeToggleIconMobile) themeToggleIconMobile.className = 'bi ' + icon;
            if (themeToggleLabel) themeToggleLabel.textContent = dark ? 'Light' : 'Dark';
            if (persist) {
                try { localStorage.setItem('zenora-admin-theme', theme); } catch (e) {}
            }
            // Let page scripts (e.g. dashboard charts) re-style instantly.
            window.dispatchEvent(new CustomEvent('zenora-theme-change', { detail: theme }));
        }

        function zenoraToggleTheme() {
            zenoraApplyTheme(zenoraCurrentTheme() === 'dark' ? 'light' : 'dark', true);
        }

        if (themeToggle) themeToggle.addEventListener('click', zenoraToggleTheme);
        if (themeToggleMobile) themeToggleMobile.addEventListener('click', zenoraToggleTheme);

        // Follow the OS theme live, but only while the admin hasn't chosen
        // explicitly (a saved preference always wins).
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                let saved = null;
                try { saved = localStorage.getItem('zenora-admin-theme'); } catch (err) {}
                if (!saved) zenoraApplyTheme(e.matches ? 'dark' : 'light', false);
            });
        }

        // Keep the toggle UI in sync with the pre-paint preference (no event —
        // page scripts read the data-bs-theme attribute when they initialise).
        if (themeToggleIcon || themeToggleIconMobile || themeToggleLabel) {
            const dark = zenoraCurrentTheme() === 'dark';
            if (themeToggleIcon) themeToggleIcon.className = 'bi ' + (dark ? 'bi-sun-fill' : 'bi-moon-stars-fill');
            if (themeToggleIconMobile) themeToggleIconMobile.className = 'bi ' + (dark ? 'bi-sun-fill' : 'bi-moon-stars-fill');
            if (themeToggleLabel) themeToggleLabel.textContent = dark ? 'Light' : 'Dark';
        }

        // ---- Sidebar collapse (icon-only rail, persisted per browser) ----
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarToggleIcon = document.getElementById('sidebarToggleIcon');

        function zenoraCurrentSidebar() {
            return document.documentElement.getAttribute('data-sidebar') === 'collapsed';
        }

        function zenoraApplySidebar(collapsed) {
            document.documentElement.setAttribute('data-sidebar', collapsed ? 'collapsed' : 'expanded');
            try { localStorage.setItem('zenora-admin-sidebar', collapsed ? 'collapsed' : 'expanded'); } catch (e) {}
            if (sidebarToggleIcon) sidebarToggleIcon.className = 'bi ' + (collapsed ? 'bi-chevron-double-right' : 'bi-chevron-double-left');
            if (sidebarToggle) {
                sidebarToggle.setAttribute('aria-expanded', String(!collapsed));
                sidebarToggle.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
                sidebarToggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            }
        }

        function zenoraToggleSidebar() {
            zenoraApplySidebar(!zenoraCurrentSidebar());
        }

        if (sidebarToggle) sidebarToggle.addEventListener('click', zenoraToggleSidebar);

        // Keep the chevron + aria in sync with the pre-paint state.
        if (sidebarToggle) {
            const collapsed = zenoraCurrentSidebar();
            sidebarToggle.setAttribute('aria-expanded', String(!collapsed));
            sidebarToggle.title = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
            sidebarToggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            if (sidebarToggleIcon) sidebarToggleIcon.className = 'bi ' + (collapsed ? 'bi-chevron-double-right' : 'bi-chevron-double-left');
        }

        // Keyboard shortcut: Ctrl+\ toggles the rail (like modern dev panels).
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === '\\') {
                e.preventDefault();
                zenoraToggleSidebar();
            }
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

        // ---- Admin notifications bell ----
        const bellBadge = document.getElementById('bellBadge');
        const notifList = document.getElementById('notifList');

        function setBellBadge(count) {
            if (!bellBadge) return;
            count = Number(count) || 0;
            bellBadge.textContent = count > 99 ? '99+' : String(count);
            bellBadge.classList.toggle('d-none', count === 0);
        }

        function refreshBellBadge() {
            fetch('{{ route('admin.notifications.unread-count') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(d => setBellBadge(d.count))
                .catch(() => {});
        }

        function markAllRead() {
            fetch('{{ route('admin.notifications.read-all') }}', {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
                .then(() => {
                    refreshBellBadge();
                    loadNotifications();
                })
                .catch(() => {});
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function loadNotifications() {
            if (!notifList) return;
            fetch('{{ route('admin.notifications.index') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(items => {
                    if (!items.length) {
                        notifList.innerHTML = '<div class="text-center text-muted py-4">No notifications</div>';
                        return;
                    }
                    notifList.innerHTML = items.map(n => {
                        const unread = n.read_at ? '' : 'bg-light';
                        const icon = n.read_at ? 'bi-check2-circle text-success' : 'bi-bell-fill text-primary';
                        // Escape everything server-supplied to prevent stored XSS
                        // (notification messages include the customer's name).
                        const title = escapeHtml(n.title);
                        const message = escapeHtml(n.message);
                        const created = escapeHtml(n.created_at);
                        const actionUrl = escapeHtml(n.action_url);
                        const viewBtn = actionUrl
                            ? `<a href="${actionUrl}" class="btn btn-sm btn-outline-primary mt-1">View order</a>`
                            : '';
                        const body = `
                            <div class="px-3 py-2 border-bottom ${unread}">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi ${icon} mt-1"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small">${title}</div>
                                        <div class="text-muted small">${message}</div>
                                        <div class="text-muted" style="font-size: .75rem;">${created}</div>
                                        ${viewBtn}
                                    </div>
                                </div>
                            </div>`;
                        return body;
                    }).join('');
                })
                .catch(() => {
                    if (notifList) notifList.innerHTML = '<div class="text-center text-muted py-4">Failed to load notifications</div>';
                });
        }

        if (bellBadge) {
            refreshBellBadge();
            setInterval(refreshBellBadge, 30000);

            const bellBtn = document.getElementById('adminBell');
            if (bellBtn) {
                bellBtn.addEventListener('click', () => {
                    setTimeout(loadNotifications, 100);
                });
            }

            const markAllBtn = document.getElementById('markAllReadBtn');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', markAllRead);
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
