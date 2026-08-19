<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sales ERP Enterprise - B2B Revenue & Fulfillment Platform</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-light: rgba(99, 102, 241, 0.15);
            --accent-cyan: #06b6d4;
            --accent-emerald: #10b981;
            --accent-amber: #f59e0b;
            --accent-rose: #f43f5e;
            --bg-dark: #090d16;
            --bg-card: #111827;
            --bg-card-hover: #172033;
            --border-color: rgba(255, 255, 255, 0.08);
            --border-glow: rgba(99, 102, 241, 0.35);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0d1322 0%, #080c16 100%);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 40;
        }

        .sidebar-brand {
            padding: 22px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
            color: white;
        }

        .brand-title {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(90deg, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .nav-section {
            padding: 16px 12px 6px;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .nav-menu {
            flex: 1;
            padding: 10px 12px;
            overflow-y: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 3px;
            text-decoration: none;
            border: 1px solid transparent;
        }

        .nav-item:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.04);
        }

        .nav-item.active {
            color: #ffffff;
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.18) 0%, rgba(6, 182, 212, 0.08) 100%);
            border-color: rgba(99, 102, 241, 0.3);
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .nav-item.active svg {
            color: var(--accent-cyan);
        }

        .nav-badge {
            margin-left: auto;
            padding: 2px 7px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            background: rgba(99, 102, 241, 0.25);
            color: #818cf8;
        }

        /* User Profile in Sidebar */
        .sidebar-user {
            padding: 16px;
            border-top: 1px solid var(--border-color);
            background: rgba(0, 0, 0, 0.2);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 13px;
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 12.5px;
            font-weight: 600;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 11px;
            color: var(--accent-cyan);
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Main Content Container */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
        }

        /* Top Header */
        .header {
            height: 70px;
            background: rgba(13, 19, 34, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .header-title-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.01em;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-input-box {
            position: relative;
            width: 260px;
        }

        .search-input-box input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 12px 8px 36px;
            font-size: 13px;
            color: white;
            outline: none;
            transition: all 0.2s;
        }

        .search-input-box input:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .search-input-box svg {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.45);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: #e2e8f0;
            border-color: var(--border-color);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 6px;
        }

        .btn-success {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.3);
        }
        .btn-success:hover {
            background: rgba(16, 185, 129, 0.25);
        }

        .btn-danger {
            background: rgba(244, 63, 94, 0.15);
            color: #fb7185;
            border-color: rgba(244, 63, 94, 0.3);
        }
        .btn-danger:hover {
            background: rgba(244, 63, 94, 0.25);
        }

        /* Content Area */
        .content-body {
            flex: 1;
            padding: 28px;
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
        }

        /* Tab Content Control */
        .tab-pane {
            display: none;
            animation: fadeIn 0.2s ease forwards;
        }

        .tab-pane.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent-cyan));
        }

        .kpi-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .kpi-label {
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .kpi-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .kpi-value {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .kpi-subtext {
            font-size: 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Charts Layout */
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }

        @media (max-width: 1100px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Table Design */
        .table-responsive {
            overflow-x: auto;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        th {
            background: rgba(0, 0, 0, 0.25);
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #cbd5e1;
            vertical-align: middle;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Status Pills */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge-success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-info { background: rgba(6, 182, 212, 0.15); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.3); }
        .badge-danger { background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
        .badge-purple { background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }
        .badge-neutral { background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); }

        /* Kanban Board Styles */
        .kanban-board {
            display: grid;
            grid-template-columns: repeat(6, minmax(260px, 1fr));
            gap: 16px;
            overflow-x: auto;
            padding-bottom: 16px;
        }

        .kanban-column {
            background: rgba(13, 19, 34, 0.6);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 14px;
            min-height: 520px;
            display: flex;
            flex-direction: column;
        }

        .kanban-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .kanban-title {
            font-size: 13px;
            font-weight: 700;
            color: #e2e8f0;
            text-transform: capitalize;
        }

        .kanban-total {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }

        .kanban-cards {
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex: 1;
        }

        .deal-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .deal-card:hover {
            border-color: var(--border-glow);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .deal-title {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .deal-customer {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .deal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid rgba(255, 255, 255, 0.04);
            padding-top: 8px;
            margin-top: 6px;
        }

        .deal-amount {
            font-size: 14px;
            font-weight: 800;
            color: var(--accent-cyan);
        }

        .deal-rep {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }

        /* Modal Backdrop & Box */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(6px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal-box {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            width: 90%;
            max-width: 760px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: modalPop 0.2s ease forwards;
        }

        @keyframes modalPop {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: rgba(0, 0, 0, 0.2);
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .form-control {
            width: 100%;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            color: #ffffff;
            outline: none;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 120;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.3s ease;
        }

        .toast.success {
            border-left: 4px solid #10b981;
        }
        .toast.error {
            border-left: 4px solid #f43f5e;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Settings Styles */
        .settings-container {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .settings-container {
                grid-template-columns: 1fr;
            }
        }

        .settings-nav {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            position: sticky;
            top: 94px;
        }

        .settings-nav-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
            background: transparent;
            border: 1px solid transparent;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s ease;
            width: 100%;
        }

        .settings-nav-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }

        .settings-nav-btn.active {
            color: #ffffff;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.25) 0%, rgba(6, 182, 212, 0.15) 100%);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .settings-section {
            display: none;
        }

        .settings-section.active {
            display: block;
            animation: fadeIn 0.25s ease forwards;
        }

        /* Modern Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            flex-shrink: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.15);
            transition: .3s;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        .toggle-switch input:checked + .toggle-slider {
            background: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%);
            border-color: rgba(99, 102, 241, 0.5);
        }

        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(20px);
        }

        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 12px;
            transition: border-color 0.2s;
        }

        .toggle-row:hover {
            border-color: rgba(255, 255, 255, 0.15);
        }

        .toggle-info h4 {
            font-size: 13px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 3px;
        }

        .toggle-info p {
            font-size: 12px;
            color: #94a3b8;
        }

        .info-stat-card {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-stat-card .label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }

        .info-stat-card .value {
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            word-break: break-all;
        }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo">
                <i data-lucide="layers"></i>
            </div>
            <div>
                <div class="brand-title">SALES ERP</div>
                <div class="brand-subtitle">Enterprise Suite</div>
            </div>
        </div>

        <div class="nav-menu">
            <div class="nav-section">Core Modules</div>
            
            <a class="nav-item active" onclick="switchTab('dashboard')">
                <i data-lucide="layout-dashboard"></i>
                <span>Executive Dashboard</span>
            </a>

            <a class="nav-item" onclick="switchTab('leads')">
                <i data-lucide="target"></i>
                <span>Lead Management</span>
                <span class="nav-badge" id="badge-leads-count">{{ $leads->count() }}</span>
            </a>

            <a class="nav-item" onclick="switchTab('opportunities')">
                <i data-lucide="kanban"></i>
                <span>Opportunity Pipeline</span>
                <span class="nav-badge" id="badge-opps-count">{{ $opportunities->count() }}</span>
            </a>

            <a class="nav-item" onclick="switchTab('quotes')">
                <i data-lucide="file-text"></i>
                <span>Quotes & Proposals</span>
                <span class="nav-badge">{{ $quotes->count() }}</span>
            </a>

            <a class="nav-item" onclick="switchTab('orders')">
                <i data-lucide="shopping-cart"></i>
                <span>Order Management</span>
                <span class="nav-badge">{{ $orders->count() }}</span>
            </a>

            <div class="nav-section">Operations & Finance</div>

            <a class="nav-item" onclick="switchTab('inventory')">
                <i data-lucide="boxes"></i>
                <span>Inventory & Warehouses</span>
                <span class="nav-badge">{{ $inventory->count() }}</span>
            </a>

            <a class="nav-item" onclick="switchTab('invoices')">
                <i data-lucide="receipt"></i>
                <span>Invoice & Billing</span>
                <span class="nav-badge">{{ $invoices->count() }}</span>
            </a>

            <a class="nav-item" onclick="switchTab('commissions')">
                <i data-lucide="award"></i>
                <span>Commission Tracker</span>
            </a>

            <div class="nav-section">CRM & Strategy</div>

            <a class="nav-item" onclick="switchTab('customers')">
                <i data-lucide="building-2"></i>
                <span>Customer Accounts</span>
                <span class="nav-badge">{{ $customers->count() }}</span>
            </a>

            <a class="nav-item" onclick="switchTab('reports')">
                <i data-lucide="bar-chart-3"></i>
                <span>Reporting & Analytics</span>
            </a>

            <div class="nav-section">System & Settings</div>

            <a class="nav-item" onclick="switchTab('settings')">
                <i data-lucide="settings"></i>
                <span>General Settings</span>
            </a>
        </div>

        <!-- User Profile & Switcher -->
        <div class="sidebar-user">
            <div class="user-card">
                <div class="user-avatar">
                    {{ substr($currentUser->name ?? 'A', 0, 1) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ $currentUser->name ?? 'Administrator' }}</div>
                    <div class="user-role">{{ ucfirst($currentUser->role ?? 'admin') }}</div>
                </div>
                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" title="Sign Out" style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3); color: #fb7185; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                        <i data-lucide="log-out" style="width: 15px; height: 15px;"></i>
                    </button>
                </form>
            </div>
            <!-- Quick Role Switcher -->
            <form action="{{ route('erp.switch-user') }}" method="POST" style="margin-top: 10px;">
                @csrf
                <select name="user_id" onchange="this.form.submit()" class="form-control" style="font-size: 11px; padding: 4px 8px; height: 28px;">
                    <option value="" disabled selected>Switch Active Role...</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ $currentUser && $currentUser->id === $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ ucfirst($u->role) }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Header -->
        <header class="header">
            <div class="header-title-box">
                <h1 class="page-title" id="page-heading">Executive Dashboard</h1>
                <span class="badge badge-info">Production v1.2</span>
            </div>

            <div class="header-actions">
                <div class="search-input-box">
                    <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                    <input type="text" placeholder="Search in active view..." id="tableSearch" onkeyup="filterActiveTable(this.value)">
                </div>

                <button class="btn btn-primary" onclick="openModal('quickActionModal')">
                    <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                    <span>Quick Create</span>
                </button>

                <form action="{{ route('logout') }}" method="POST" style="display: inline-block; margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="color: #fb7185; border-color: rgba(244, 63, 94, 0.25);">
                        <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Body Content Tabs -->
        <main class="content-body">

            <!-- TAB 1: EXECUTIVE DASHBOARD -->
            <div id="tab-dashboard" class="tab-pane active">
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Total Revenue</span>
                            <div class="kpi-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                                <i data-lucide="dollar-sign"></i>
                            </div>
                        </div>
                        <div class="kpi-value">${{ number_format($metrics['total_revenue'], 2) }}</div>
                        <div class="kpi-subtext" style="color: #34d399;">↑ 18.4% vs prior period</div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Active Pipeline</span>
                            <div class="kpi-icon-box" style="background: rgba(6, 182, 212, 0.15); color: #22d3ee;">
                                <i data-lucide="activity"></i>
                            </div>
                        </div>
                        <div class="kpi-value">${{ number_format($metrics['total_pipeline'], 2) }}</div>
                        <div class="kpi-subtext">{{ $metrics['active_deals'] }} active qualified deals</div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Win Rate</span>
                            <div class="kpi-icon-box" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">
                                <i data-lucide="trophy"></i>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $metrics['win_rate'] }}%</div>
                        <div class="kpi-subtext">{{ $metrics['closed_won_deals'] }} closed won deals</div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Total Leads</span>
                            <div class="kpi-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                                <i data-lucide="target"></i>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $metrics['total_leads'] }}</div>
                        <div class="kpi-subtext">{{ $metrics['new_leads'] }} new inbound leads</div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-row">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i data-lucide="trending-up" style="color: var(--accent-cyan);"></i>
                                Revenue & Pipeline Growth Trend
                            </div>
                            <span class="badge badge-info">Trailing 6 Months</span>
                        </div>
                        <div style="height: 290px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i data-lucide="pie-chart" style="color: var(--primary);"></i>
                                Pipeline Stage Distribution
                            </div>
                        </div>
                        <div style="height: 290px;">
                            <canvas id="pipelineChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Leaderboard & Activity Feed -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i data-lucide="award" style="color: var(--accent-amber);"></i>
                                Top Sales Reps Leaderboard
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sales Rep</th>
                                        <th>Role</th>
                                        <th>Won</th>
                                        <th>Booked Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topPerformers as $rep)
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600; color: white;">{{ $rep['name'] }}</div>
                                            <div style="font-size: 11px; color: #64748b;">{{ $rep['email'] }}</div>
                                        </td>
                                        <td><span class="badge badge-neutral">{{ $rep['role'] }}</span></td>
                                        <td><span class="badge badge-success">{{ $rep['deals_won'] }} Won</span></td>
                                        <td style="font-weight: 700; color: #34d399;">${{ number_format($rep['total_sales'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i data-lucide="clock" style="color: var(--accent-cyan);"></i>
                                Live Activity Timeline
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 12px; max-height: 280px; overflow-y: auto;">
                            @foreach($activities as $act)
                            <div style="display: flex; gap: 12px; padding: 10px; border-radius: 8px; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color);">
                                <div style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: rgba(99,102,241,0.2); color: var(--primary); flex-shrink: 0;">
                                    <i data-lucide="{{ $act->type === 'call' ? 'phone' : ($act->type === 'email' ? 'mail' : ($act->type === 'meeting' ? 'users' : 'check-square')) }}" style="width: 16px; height: 16px;"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 12px; font-weight: 600; color: white;">{{ $act->subject }}</div>
                                    <div style="font-size: 11px; color: #94a3b8;">{{ Str::limit($act->description, 60) }}</div>
                                    <div style="font-size: 10px; color: #64748b; margin-top: 3px;">
                                        By {{ $act->performer->name ?? 'System' }} • {{ $act->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: LEADS MANAGEMENT -->
            <div id="tab-leads" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i data-lucide="target" style="color: var(--accent-amber);"></i>
                            Lead Pipeline & Inbound Capture
                        </div>
                        <button class="btn btn-primary" onclick="openModal('createLeadModal')">
                            <i data-lucide="plus"></i> Add New Lead
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="table-leads">
                            <thead>
                                <tr>
                                    <th>Lead Title / Prospect</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Est. Value</th>
                                    <th>Assigned To</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leads as $lead)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: white;">{{ $lead->title }}</div>
                                        <div style="font-size: 11px; color: #94a3b8;">{{ $lead->company_name }} ({{ $lead->contact_name }}) • {{ $lead->email }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $lead->source)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $lead->status === 'converted' ? 'badge-success' : ($lead->status === 'qualified' ? 'badge-purple' : 'badge-warning') }}">
                                            {{ ucfirst($lead->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 50px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden;">
                                                <div style="width: {{ $lead->qualification_score }}%; height: 100%; background: linear-gradient(90deg, #6366f1, #10b981);"></div>
                                            </div>
                                            <span style="font-size: 11px; font-weight: 600;">{{ $lead->qualification_score }}</span>
                                        </div>
                                    </td>
                                    <td style="font-weight: 700; color: #34d399;">
                                        ${{ number_format($lead->estimated_value, 2) }}
                                    </td>
                                    <td>{{ $lead->assignedUser->name ?? 'Unassigned' }}</td>
                                    <td>
                                        @if($lead->status !== 'converted')
                                        <button class="btn btn-sm btn-success" onclick="openConvertModal({{ $lead->id }}, '{{ addslashes($lead->company_name ?: $lead->title) }}', {{ $lead->estimated_value }})">
                                            <i data-lucide="arrow-right-circle" style="width: 14px; height: 14px;"></i> Convert Deal
                                        </button>
                                        @else
                                        <span class="badge badge-success">Converted ✓</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 3: OPPORTUNITY KANBAN PIPELINE -->
            <div id="tab-opportunities" class="tab-pane">
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-header" style="margin-bottom: 0; border: none; padding: 0;">
                        <div class="card-title">
                            <i data-lucide="kanban" style="color: var(--accent-cyan);"></i>
                            Deals Pipeline (Kanban Workflow)
                        </div>
                        <button class="btn btn-primary" onclick="openModal('createOppModal')">
                            <i data-lucide="plus"></i> New Opportunity
                        </button>
                    </div>
                </div>

                <div class="kanban-board">
                    @php
                        $stages = [
                            'prospecting' => ['title' => 'Prospecting', 'color' => '#64748b'],
                            'qualification' => ['title' => 'Qualification', 'color' => '#3b82f6'],
                            'proposal' => ['title' => 'Proposal Sent', 'color' => '#8b5cf6'],
                            'negotiation' => ['title' => 'Negotiation', 'color' => '#f59e0b'],
                            'closed_won' => ['title' => 'Closed Won', 'color' => '#10b981'],
                            'closed_lost' => ['title' => 'Closed Lost', 'color' => '#f43f5e'],
                        ];
                    @endphp

                    @foreach($stages as $stageKey => $sInfo)
                    @php
                        $stageOpps = $opportunities->where('stage', $stageKey);
                        $stageTotal = $stageOpps->sum('amount');
                    @endphp
                    <div class="kanban-column">
                        <div class="kanban-header">
                            <div class="kanban-title" style="border-left: 3px solid {{ $sInfo['color'] }}; padding-left: 8px;">
                                {{ $sInfo['title'] }} ({{ $stageOpps->count() }})
                            </div>
                            <div class="kanban-total">${{ number_format($stageTotal, 0) }}</div>
                        </div>

                        <div class="kanban-cards">
                            @foreach($stageOpps as $opp)
                            <div class="deal-card" onclick="openDealDetailModal({{ $opp->id }}, '{{ addslashes($opp->title) }}', {{ $opp->amount }}, '{{ $opp->stage }}', '{{ addslashes($opp->customer->company_name ?? 'N/A') }}', {{ $opp->probability }})">
                                <div class="deal-title">{{ $opp->title }}</div>
                                <div class="deal-customer">
                                    <i data-lucide="building" style="width: 13px; height: 13px;"></i>
                                    <span>{{ $opp->customer->company_name ?? 'N/A' }}</span>
                                </div>
                                <div style="display: flex; gap: 6px; margin-bottom: 8px;">
                                    <span class="badge badge-info">{{ $opp->probability }}% Prob</span>
                                    <span class="badge badge-neutral">{{ $opp->opportunityProducts->count() }} Products</span>
                                </div>
                                <div class="deal-footer">
                                    <div class="deal-amount">${{ number_format($opp->amount, 2) }}</div>
                                    <div class="deal-rep">{{ $opp->assignedUser->name ?? 'Rep' }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB 4: QUOTES & PROPOSALS -->
            <div id="tab-quotes" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i data-lucide="file-text" style="color: var(--primary);"></i>
                            Quotes & Pricing Proposals
                        </div>
                        <button class="btn btn-primary" onclick="openModal('createQuoteModal')">
                            <i data-lucide="plus"></i> Generate Quote
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="table-quotes">
                            <thead>
                                <tr>
                                    <th>Quote Number</th>
                                    <th>Customer Account</th>
                                    <th>Status</th>
                                    <th>Valid Until</th>
                                    <th>Line Items</th>
                                    <th>Total Value</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotes as $q)
                                <tr>
                                    <td style="font-weight: 700; color: white;">{{ $q->quote_number }}</td>
                                    <td>{{ $q->customer->company_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $q->status === 'converted' ? 'badge-success' : ($q->status === 'sent' ? 'badge-info' : ($q->status === 'accepted' ? 'badge-purple' : 'badge-warning')) }}">
                                            {{ ucfirst($q->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $q->valid_until ? $q->valid_until->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $q->items->count() }} items</td>
                                    <td style="font-weight: 700; color: #34d399;">${{ number_format($q->total, 2) }}</td>
                                    <td>
                                        <div style="display: flex; gap: 6px;">
                                            <button class="btn btn-sm btn-secondary" onclick="viewQuotePdf({{ $q->id }}, '{{ $q->quote_number }}')">
                                                <i data-lucide="printer" style="width: 14px; height: 14px;"></i> View PDF
                                            </button>
                                            @if($q->status !== 'converted')
                                            <button class="btn btn-sm btn-success" onclick="convertQuoteToOrder({{ $q->id }})">
                                                <i data-lucide="check" style="width: 14px; height: 14px;"></i> Convert to Order
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 5: ORDERS & FULFILLMENT -->
            <div id="tab-orders" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i data-lucide="shopping-cart" style="color: var(--accent-emerald);"></i>
                            Sales Orders & Delivery Fulfillment
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table-orders">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Fulfillment Status</th>
                                    <th>Expected Delivery</th>
                                    <th>Total Value</th>
                                    <th>Invoiced</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td style="font-weight: 700; color: white;">{{ $order->order_number }}</td>
                                    <td>{{ $order->customer->company_name ?? 'N/A' }}</td>
                                    <td>
                                        <select onchange="updateOrderStatus({{ $order->id }}, this.value)" class="form-control" style="font-size: 11px; padding: 4px 8px; width: 130px; height: 28px;">
                                            @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $st)
                                                <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>{{ $order->expected_delivery_date ? $order->expected_delivery_date->format('M d, Y') : 'N/A' }}</td>
                                    <td style="font-weight: 700; color: #34d399;">${{ number_format($order->total, 2) }}</td>
                                    <td>
                                        @if($order->invoices->count() > 0)
                                            <span class="badge badge-success">Invoiced ({{ $order->invoices->first()->invoice_number }})</span>
                                        @else
                                            <span class="badge badge-warning">Pending Invoice</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->invoices->count() === 0)
                                        <button class="btn btn-sm btn-primary" onclick="generateOrderInvoice({{ $order->id }})">
                                            <i data-lucide="receipt" style="width: 14px; height: 14px;"></i> Generate Invoice
                                        </button>
                                        @else
                                        <button class="btn btn-sm btn-secondary" onclick="switchTab('invoices')">
                                            <i data-lucide="eye" style="width: 14px; height: 14px;"></i> View Invoice
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 6: INVOICES & BILLING -->
            <div id="tab-invoices" class="tab-pane">
                <!-- Invoice KPI Summary -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Total Invoiced</span>
                            <div class="kpi-icon-box" style="background: rgba(6, 182, 212, 0.15); color: #22d3ee;">
                                <i data-lucide="file-check"></i>
                            </div>
                        </div>
                        <div class="kpi-value">${{ number_format($invoices->sum('total'), 2) }}</div>
                        <div class="kpi-subtext">{{ $invoices->count() }} total billed invoices</div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Collected Payments</span>
                            <div class="kpi-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                                <i data-lucide="check-circle-2"></i>
                            </div>
                        </div>
                        <div class="kpi-value">${{ number_format($invoices->sum('amount_paid'), 2) }}</div>
                        <div class="kpi-subtext" style="color: #34d399;">{{ $invoices->where('status', 'paid')->count() }} fully paid invoices</div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Outstanding Balance</span>
                            <div class="kpi-icon-box" style="background: rgba(244, 63, 94, 0.15); color: #fb7185;">
                                <i data-lucide="alert-triangle"></i>
                            </div>
                        </div>
                        <div class="kpi-value">${{ number_format($invoices->sum('balance_due'), 2) }}</div>
                        <div class="kpi-subtext">Receivables pending collection</div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Overdue Count</span>
                            <div class="kpi-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                                <i data-lucide="clock"></i>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $invoices->where('status', 'overdue')->count() }}</div>
                        <div class="kpi-subtext">Past due payment terms</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i data-lucide="receipt" style="color: var(--accent-cyan);"></i>
                            Tax Invoices & Payment Reconciliation
                        </div>
                        <button class="btn btn-primary" onclick="openModal('createInvoiceModal')">
                            <i data-lucide="plus"></i> Generate Direct Invoice
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="table-invoices">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer Account</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Total ($)</th>
                                    <th>Paid ($)</th>
                                    <th>Balance Due ($)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $inv)
                                <tr>
                                    <td style="font-weight: 700; color: white;">{{ $inv->invoice_number }}</td>
                                    <td>{{ $inv->customer->company_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $inv->status === 'paid' ? 'badge-success' : ($inv->status === 'partial' ? 'badge-warning' : ($inv->status === 'overdue' ? 'badge-danger' : 'badge-info')) }}">
                                            {{ ucfirst($inv->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $inv->due_date ? $inv->due_date->format('M d, Y') : 'N/A' }}</td>
                                    <td style="font-weight: 600;">${{ number_format($inv->total, 2) }}</td>
                                    <td style="color: #34d399; font-weight: 600;">${{ number_format($inv->amount_paid, 2) }}</td>
                                    <td style="color: #fb7185; font-weight: 700;">${{ number_format($inv->balance_due, 2) }}</td>
                                    <td>
                                        <div style="display: flex; gap: 6px;">
                                            @if($inv->balance_due > 0)
                                            <button class="btn btn-sm btn-success" onclick="openPaymentModal({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $inv->balance_due }})">
                                                <i data-lucide="credit-card" style="width: 14px; height: 14px;"></i> Pay
                                            </button>
                                            @endif
                                            <button class="btn btn-sm btn-secondary" onclick="viewInvoicePdf({{ $inv->id }}, '{{ $inv->invoice_number }}')">
                                                <i data-lucide="printer" style="width: 14px; height: 14px;"></i> PDF
                                            </button>
                                            @if($inv->status === 'draft')
                                            <button class="btn btn-sm btn-secondary" onclick="sendInvoice({{ $inv->id }})">
                                                <i data-lucide="send" style="width: 14px; height: 14px;"></i> Send
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 7: INVENTORY & WAREHOUSES -->
            <div id="tab-inventory" class="tab-pane">
                <!-- Inventory KPI Summary -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Active SKUs</span>
                            <div class="kpi-icon-box" style="background: rgba(6, 182, 212, 0.15); color: #22d3ee;">
                                <i data-lucide="package"></i>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $products->where('type', 'product')->count() }}</div>
                        <div class="kpi-subtext">Tracked hardware/goods products</div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Total Stock Units</span>
                            <div class="kpi-icon-box" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">
                                <i data-lucide="boxes"></i>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $inventory->sum('quantity') }}</div>
                        <div class="kpi-subtext">Across {{ $warehouses->count() }} regional depot hubs</div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Reserved Units</span>
                            <div class="kpi-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                                <i data-lucide="lock"></i>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $inventory->sum('reserved_quantity') }}</div>
                        <div class="kpi-subtext">Allocated to pending orders</div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-top">
                            <span class="kpi-label">Warehouse Depots</span>
                            <div class="kpi-icon-box" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">
                                <i data-lucide="warehouse"></i>
                            </div>
                        </div>
                        <div class="kpi-value">{{ $warehouses->count() }}</div>
                        <div class="kpi-subtext">US West, East, EMEA & APAC</div>
                    </div>
                </div>

                <div class="card" style="background: linear-gradient(135deg, rgba(6,182,212,0.1) 0%, rgba(99,102,241,0.05) 100%); border-color: rgba(6,182,212,0.3); margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: #ffffff;">Multi-Warehouse Logistics Hub</h3>
                            <p style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Manage stock balance, inter-warehouse replenishment, and physical inventory counts.</p>
                        </div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <button class="btn btn-secondary" onclick="openModal('createProductModal')">
                                <i data-lucide="plus"></i> Add Product / SKU
                            </button>
                            <button class="btn btn-secondary" onclick="openModal('stockInModal')">
                                <i data-lucide="plus-circle"></i> Stock In / Restock
                            </button>
                            <button class="btn btn-primary" onclick="openModal('transferStockModal')">
                                <i data-lucide="arrow-left-right"></i> Inter-Warehouse Transfer
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i data-lucide="boxes" style="color: var(--accent-emerald);"></i>
                            Live Stock Levels Across Warehouse Hubs
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table-inventory">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Product Name</th>
                                    <th>Warehouse Hub</th>
                                    <th>Available Stock</th>
                                    <th>Reserved</th>
                                    <th>Reorder Point</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventory as $invItem)
                                <tr>
                                    <td style="font-weight: 700; color: var(--accent-cyan);">{{ $invItem->product->sku ?? 'N/A' }}</td>
                                    <td style="font-weight: 600; color: white;">{{ $invItem->product->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $invItem->warehouse->name ?? 'Warehouse' }}</span>
                                    </td>
                                    <td style="font-size: 14px; font-weight: 700; color: white;">{{ $invItem->quantity }} {{ $invItem->product->unit ?? 'units' }}</td>
                                    <td>{{ $invItem->reserved_quantity }}</td>
                                    <td>{{ $invItem->product->reorder_point ?? 0 }}</td>
                                    <td>
                                        @if($invItem->quantity <= ($invItem->product->min_stock_level ?? 0))
                                            <span class="badge badge-danger">Low Stock Alert</span>
                                        @else
                                            <span class="badge badge-success">Optimal Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px;">
                                            <button class="btn btn-sm btn-secondary" onclick="openAdjustStockModal({{ $invItem->id }}, '{{ addslashes($invItem->product->name ?? '') }}', '{{ addslashes($invItem->warehouse->name ?? '') }}', {{ $invItem->quantity }})">
                                                <i data-lucide="sliders" style="width: 14px; height: 14px;"></i> Adjust
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="openTransferWithItem({{ $invItem->product_id }}, {{ $invItem->warehouse_id }})">
                                                <i data-lucide="arrow-left-right" style="width: 14px; height: 14px;"></i> Transfer
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 8: COMMISSION TRACKER -->
            <div id="tab-commissions" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i data-lucide="award" style="color: var(--accent-amber);"></i>
                            Sales Commission & Incentive Payouts
                        </div>
                        <button class="btn btn-primary" onclick="openModal('calculateCommissionModal')">
                            <i data-lucide="calculator"></i> Calculate Month Commission
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="table-commissions">
                            <thead>
                                <tr>
                                    <th>Sales Rep</th>
                                    <th>Period</th>
                                    <th>Total Booked Sales</th>
                                    <th>Comm. Rate</th>
                                    <th>Commission</th>
                                    <th>Bonus Tier</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commissions as $comm)
                                <tr>
                                    <td style="font-weight: 700; color: white;">{{ $comm->user->name ?? 'Rep' }}</td>
                                    <td><span class="badge badge-info">{{ $comm->period }} ({{ $comm->period_type }})</span></td>
                                    <td style="font-weight: 700; color: #ffffff;">${{ number_format($comm->total_sales, 2) }}</td>
                                    <td>{{ $comm->commission_rate }}%</td>
                                    <td style="font-weight: 700; color: #34d399;">${{ number_format($comm->commission_amount, 2) }}</td>
                                    <td style="color: #fbbf24; font-weight: 600;">+${{ number_format($comm->bonus_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $comm->status === 'paid' ? 'badge-success' : ($comm->status === 'approved' ? 'badge-purple' : 'badge-warning') }}">
                                            {{ ucfirst($comm->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($comm->status === 'pending')
                                        <button class="btn btn-sm btn-success" onclick="approveCommission({{ $comm->id }})">Approve</button>
                                        @elseif($comm->status === 'approved')
                                        <button class="btn btn-sm btn-primary" onclick="payCommission({{ $comm->id }})">Mark Paid</button>
                                        @else
                                        <span class="badge badge-success">Disbursed ✓</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 9: CUSTOMER ACCOUNTS -->
            <div id="tab-customers" class="tab-pane">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i data-lucide="building-2" style="color: var(--primary);"></i>
                            Enterprise & SME Accounts
                        </div>
                        <button class="btn btn-primary" onclick="openModal('createCustomerModal')">
                            <i data-lucide="plus"></i> Add New Account
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="table-customers">
                            <thead>
                                <tr>
                                    <th>Company Account</th>
                                    <th>GST / Tax ID</th>
                                    <th>Tier / Type</th>
                                    <th>Key Contacts</th>
                                    <th>Credit Limit</th>
                                    <th>Payment Terms</th>
                                    <th>Assigned Rep</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $c)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: white;">{{ $c->company_name }}</div>
                                        <div style="font-size: 11px; color: #94a3b8;">{{ $c->industry }} • {{ $c->address_city ?? 'City' }}, {{ $c->address_country ?? 'USA' }}</div>
                                    </td>
                                    <td><code style="color: var(--accent-cyan); font-size: 11px;">{{ $c->gst_number ?: ($c->pan_number ?: 'N/A') }}</code></td>
                                    <td><span class="badge badge-purple">{{ ucfirst(str_replace('_', ' ', $c->type)) }}</span></td>
                                    <td>
                                        @foreach($c->contacts as $contact)
                                        <div style="font-size: 12px; color: #e2e8f0;">
                                            <strong>{{ $contact->first_name }} {{ $contact->last_name }}</strong> ({{ $contact->designation }})
                                        </div>
                                        @endforeach
                                    </td>
                                    <td style="font-weight: 600;">${{ number_format($c->credit_limit, 2) }}</td>
                                    <td><span class="badge badge-neutral">{{ strtoupper(str_replace('_', ' ', $c->payment_terms)) }}</span></td>
                                    <td>{{ $c->assignedUser->name ?? 'Rep' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 10: REPORTS & ANALYTICS -->
            <div id="tab-reports" class="tab-pane">
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-label">Annual Revenue Run Rate</div>
                        <div class="kpi-value">${{ number_format($metrics['total_revenue'] * 12, 2) }}</div>
                        <div class="kpi-subtext">Projected ARR</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Weighted Deal Pipeline</div>
                        <div class="kpi-value">${{ number_format($metrics['weighted_pipeline'], 2) }}</div>
                        <div class="kpi-subtext">Probability adjusted</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Active Customer Accounts</div>
                        <div class="kpi-value">{{ $metrics['total_customers'] }}</div>
                        <div class="kpi-subtext">Retained accounts</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i data-lucide="globe" style="color: var(--accent-cyan);"></i>
                                Territory Sales Performance
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Territory</th>
                                        <th>Region</th>
                                        <th>Customers</th>
                                        <th>Total Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($territoryPerformance as $t)
                                    <tr>
                                        <td style="font-weight: 700; color: white;">{{ $t['name'] }}</td>
                                        <td>{{ $t['region'] }}</td>
                                        <td>{{ $t['customers_count'] }}</td>
                                        <td style="font-weight: 700; color: #34d399;">${{ number_format($t['total_sales'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i data-lucide="package" style="color: var(--primary);"></i>
                                Product Performance & Volume
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Units Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productPerformance as $prod)
                                    <tr>
                                        <td style="font-weight: 700; color: white;">{{ $prod['name'] }}</td>
                                        <td>{{ $prod['category'] }}</td>
                                        <td>{{ $prod['units_sold'] }} units</td>
                                        <td style="font-weight: 700; color: #34d399;">${{ number_format($prod['revenue_generated'], 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 11: GENERAL SETTINGS -->
            <div id="tab-settings" class="tab-pane">
                <!-- Settings Header Banner & Quick Actions -->
                <div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, rgba(17, 24, 39, 0.95) 0%, rgba(13, 19, 34, 0.95) 100%); border: 1px solid rgba(99, 102, 241, 0.25);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(6, 182, 212, 0.2) 100%); border: 1px solid rgba(99, 102, 241, 0.4); display: flex; align-items: center; justify-content: center; color: #818cf8;">
                                <i data-lucide="sliders" style="width: 24px; height: 24px;"></i>
                            </div>
                            <div>
                                <h2 style="font-size: 18px; font-weight: 800; color: #ffffff; margin-bottom: 2px;">General & System Settings</h2>
                                <p style="font-size: 12px; color: #94a3b8;">Configure organization profile, regional localization, sales numbering prefixes, inventory thresholds, and notifications.</p>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button type="button" class="btn btn-secondary" onclick="clearSystemCache()" id="btnClearCache" style="font-size: 12px;">
                                <i data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i> Purge Cache
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetSettingsToDefault()" style="color: #fb7185; border-color: rgba(244, 63, 94, 0.3); font-size: 12px;">
                                <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i> Reset Defaults
                            </button>
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('formGeneralSettings').requestSubmit();" id="btnSaveSettingsTop" style="font-size: 13px;">
                                <i data-lucide="check" style="width: 15px; height: 15px;"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Settings Container with Sub-Navigation and Forms -->
                <form id="formGeneralSettings" onsubmit="submitSettingsForm(event)">
                    <div class="settings-container">
                        <!-- Left Sub-Navigation Menu -->
                        <div class="settings-nav">
                            <button type="button" class="settings-nav-btn active" id="btn-subnav-company" onclick="switchSettingsSubSection('company')">
                                <i data-lucide="building" style="width: 16px; height: 16px; color: #818cf8;"></i>
                                <span>Company Profile</span>
                            </button>
                            <button type="button" class="settings-nav-btn" id="btn-subnav-localization" onclick="switchSettingsSubSection('localization')">
                                <i data-lucide="globe-2" style="width: 16px; height: 16px; color: #22d3ee;"></i>
                                <span>Localization & Currency</span>
                            </button>
                            <button type="button" class="settings-nav-btn" id="btn-subnav-sales" onclick="switchSettingsSubSection('sales')">
                                <i data-lucide="file-check" style="width: 16px; height: 16px; color: #34d399;"></i>
                                <span>Sales & Invoicing</span>
                            </button>
                            <button type="button" class="settings-nav-btn" id="btn-subnav-inventory" onclick="switchSettingsSubSection('inventory')">
                                <i data-lucide="boxes" style="width: 16px; height: 16px; color: #fbbf24;"></i>
                                <span>Inventory & Stock</span>
                            </button>
                            <button type="button" class="settings-nav-btn" id="btn-subnav-notifications" onclick="switchSettingsSubSection('notifications')">
                                <i data-lucide="bell-ring" style="width: 16px; height: 16px; color: #c084fc;"></i>
                                <span>Notification Alerts</span>
                            </button>
                            <button type="button" class="settings-nav-btn" id="btn-subnav-system" onclick="switchSettingsSubSection('system')">
                                <i data-lucide="cpu" style="width: 16px; height: 16px; color: #94a3b8;"></i>
                                <span>System Diagnostics</span>
                            </button>
                        </div>

                        <!-- Right Form Sub-Panes -->
                        <div class="settings-content">

                            <!-- SECTION 1: Company Profile -->
                            <div id="settings-section-company" class="settings-section active">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <i data-lucide="building" style="color: var(--primary);"></i>
                                            Company Identity & Official Details
                                        </div>
                                        <span class="badge badge-info">Profile Info</span>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Legal Registered Entity Name</label>
                                            <input type="text" name="company_name" id="set_company_name" class="form-control" value="{{ $settings['company_name'] ?? 'Global B2B Solutions Inc.' }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Tagline / Brand Subtitle</label>
                                            <input type="text" name="company_tagline" id="set_company_tagline" class="form-control" value="{{ $settings['company_tagline'] ?? 'Enterprise B2B Revenue & Fulfillment Platform' }}">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Corporate Email Address</label>
                                            <input type="email" name="company_email" id="set_company_email" class="form-control" value="{{ $settings['company_email'] ?? 'admin@saleserp.enterprise' }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Official Phone / Hotline</label>
                                            <input type="text" name="company_phone" id="set_company_phone" class="form-control" value="{{ $settings['company_phone'] ?? '+1 (800) 555-0199' }}">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Corporate Website URL</label>
                                            <input type="url" name="company_website" id="set_company_website" class="form-control" value="{{ $settings['company_website'] ?? 'https://saleserp.enterprise' }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">GST / Tax Identification Number</label>
                                            <input type="text" name="tax_id" id="set_tax_id" class="form-control" value="{{ $settings['tax_id'] ?? 'US-TAX-88902148' }}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Headquarters Street Address</label>
                                        <input type="text" name="company_address" id="set_company_address" class="form-control" value="{{ $settings['company_address'] ?? '100 Enterprise Way, Suite 400' }}">
                                    </div>

                                    <div class="form-row" style="grid-template-columns: 1.2fr 1fr 1fr 1fr;">
                                        <div class="form-group">
                                            <label class="form-label">City</label>
                                            <input type="text" name="company_city" id="set_company_city" class="form-control" value="{{ $settings['company_city'] ?? 'San Francisco' }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">State / Province</label>
                                            <input type="text" name="company_state" id="set_company_state" class="form-control" value="{{ $settings['company_state'] ?? 'California' }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">ZIP / Postal Code</label>
                                            <input type="text" name="company_postal_code" id="set_company_postal_code" class="form-control" value="{{ $settings['company_postal_code'] ?? '94105' }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Country</label>
                                            <input type="text" name="company_country" id="set_company_country" class="form-control" value="{{ $settings['company_country'] ?? 'United States' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: Localization & Currency -->
                            <div id="settings-section-localization" class="settings-section">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <i data-lucide="globe-2" style="color: var(--accent-cyan);"></i>
                                            Localization, Currency & Regional Formats
                                        </div>
                                        <span class="badge badge-info">Regional Defaults</span>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Base Currency Code</label>
                                            <select name="default_currency" id="set_default_currency" class="form-control">
                                                <option value="USD" {{ ($settings['default_currency'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD - United States Dollar ($)</option>
                                                <option value="EUR" {{ ($settings['default_currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR - Euro (€)</option>
                                                <option value="GBP" {{ ($settings['default_currency'] ?? '') === 'GBP' ? 'selected' : '' }}>GBP - British Pound (£)</option>
                                                <option value="INR" {{ ($settings['default_currency'] ?? '') === 'INR' ? 'selected' : '' }}>INR - Indian Rupee (₹)</option>
                                                <option value="CAD" {{ ($settings['default_currency'] ?? '') === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar (CA$)</option>
                                                <option value="AUD" {{ ($settings['default_currency'] ?? '') === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar (AU$)</option>
                                                <option value="SGD" {{ ($settings['default_currency'] ?? '') === 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar (S$)</option>
                                                <option value="AED" {{ ($settings['default_currency'] ?? '') === 'AED' ? 'selected' : '' }}>AED - UAE Dirham (AED)</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Currency Symbol</label>
                                            <input type="text" name="currency_symbol" id="set_currency_symbol" class="form-control" value="{{ $settings['currency_symbol'] ?? '$' }}" required>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Currency Symbol Placement</label>
                                            <select name="currency_position" id="set_currency_position" class="form-control">
                                                <option value="prefix" {{ ($settings['currency_position'] ?? 'prefix') === 'prefix' ? 'selected' : '' }}>Prefix (e.g. $1,000.00)</option>
                                                <option value="suffix" {{ ($settings['currency_position'] ?? '') === 'suffix' ? 'selected' : '' }}>Suffix (e.g. 1,000.00 $)</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Default System Timezone</label>
                                            <select name="timezone" id="set_timezone" class="form-control">
                                                <option value="America/New_York" {{ ($settings['timezone'] ?? 'America/New_York') === 'America/New_York' ? 'selected' : '' }}>Eastern Time (US & Canada - UTC-5)</option>
                                                <option value="America/Chicago" {{ ($settings['timezone'] ?? '') === 'America/Chicago' ? 'selected' : '' }}>Central Time (US & Canada - UTC-6)</option>
                                                <option value="America/Denver" {{ ($settings['timezone'] ?? '') === 'America/Denver' ? 'selected' : '' }}>Mountain Time (US & Canada - UTC-7)</option>
                                                <option value="America/Los_Angeles" {{ ($settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (US & Canada - UTC-8)</option>
                                                <option value="UTC" {{ ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                                <option value="Europe/London" {{ ($settings['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' }}>London, Dublin (UTC+0 / UTC+1)</option>
                                                <option value="Europe/Paris" {{ ($settings['timezone'] ?? '') === 'Europe/Paris' ? 'selected' : '' }}>Paris, Berlin, Amsterdam (UTC+1 / UTC+2)</option>
                                                <option value="Asia/Dubai" {{ ($settings['timezone'] ?? '') === 'Asia/Dubai' ? 'selected' : '' }}>Dubai, Abu Dhabi (UTC+4)</option>
                                                <option value="Asia/Kolkata" {{ ($settings['timezone'] ?? '') === 'Asia/Kolkata' ? 'selected' : '' }}>India Standard Time (IST - UTC+5:30)</option>
                                                <option value="Asia/Singapore" {{ ($settings['timezone'] ?? '') === 'Asia/Singapore' ? 'selected' : '' }}>Singapore, Hong Kong (UTC+8)</option>
                                                <option value="Asia/Tokyo" {{ ($settings['timezone'] ?? '') === 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo, Seoul (UTC+9)</option>
                                                <option value="Australia/Sydney" {{ ($settings['timezone'] ?? '') === 'Australia/Sydney' ? 'selected' : '' }}>Sydney, Melbourne (UTC+10 / UTC+11)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Date Display Format</label>
                                            <select name="date_format" id="set_date_format" class="form-control">
                                                <option value="Y-m-d" {{ ($settings['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2026-08-19)</option>
                                                <option value="d/m/Y" {{ ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (19/08/2026)</option>
                                                <option value="m/d/Y" {{ ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (08/19/2026)</option>
                                                <option value="M d, Y" {{ ($settings['date_format'] ?? '') === 'M d, Y' ? 'selected' : '' }}>Month DD, YYYY (Aug 19, 2026)</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Financial Year Start (MM-DD)</label>
                                            <input type="text" name="financial_year_start" id="set_financial_year_start" class="form-control" value="{{ $settings['financial_year_start'] ?? '01-01' }}" placeholder="01-01 or 04-01">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 3: Sales & Invoicing -->
                            <div id="settings-section-sales" class="settings-section">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <i data-lucide="file-check" style="color: #34d399;"></i>
                                            Sales Numbering, Tax Defaults & Commissions
                                        </div>
                                        <span class="badge badge-info">Automation</span>
                                    </div>

                                    <div class="form-row" style="grid-template-columns: 1fr 1fr 1fr;">
                                        <div class="form-group">
                                            <label class="form-label">Quote Number Prefix</label>
                                            <input type="text" name="quote_prefix" id="set_quote_prefix" class="form-control" value="{{ $settings['quote_prefix'] ?? 'QT-' }}" required>
                                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">e.g. {{ $settings['quote_prefix'] ?? 'QT-' }}2026-0001</div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Sales Order Prefix</label>
                                            <input type="text" name="order_prefix" id="set_order_prefix" class="form-control" value="{{ $settings['order_prefix'] ?? 'SO-' }}" required>
                                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">e.g. {{ $settings['order_prefix'] ?? 'SO-' }}2026-0001</div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Tax Invoice Prefix</label>
                                            <input type="text" name="invoice_prefix" id="set_invoice_prefix" class="form-control" value="{{ $settings['invoice_prefix'] ?? 'INV-' }}" required>
                                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">e.g. {{ $settings['invoice_prefix'] ?? 'INV-' }}2026-0001</div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Default Sales Tax Rate (%)</label>
                                            <input type="number" step="0.01" min="0" max="100" name="default_tax_rate" id="set_default_tax_rate" class="form-control" value="{{ $settings['default_tax_rate'] ?? '10.00' }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Default Standard Payment Terms</label>
                                            <select name="default_payment_terms" id="set_default_payment_terms" class="form-control">
                                                <option value="due_on_receipt" {{ ($settings['default_payment_terms'] ?? '') === 'due_on_receipt' ? 'selected' : '' }}>Due on Receipt</option>
                                                <option value="net_15" {{ ($settings['default_payment_terms'] ?? '') === 'net_15' ? 'selected' : '' }}>Net 15 Days</option>
                                                <option value="net_30" {{ ($settings['default_payment_terms'] ?? 'net_30') === 'net_30' ? 'selected' : '' }}>Net 30 Days</option>
                                                <option value="net_60" {{ ($settings['default_payment_terms'] ?? '') === 'net_60' ? 'selected' : '' }}>Net 60 Days</option>
                                                <option value="net_90" {{ ($settings['default_payment_terms'] ?? '') === 'net_90' ? 'selected' : '' }}>Net 90 Days</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Default Sales Rep Commission Rate (%)</label>
                                            <input type="number" step="0.1" min="0" max="100" name="default_commission_rate" id="set_default_commission_rate" class="form-control" value="{{ $settings['default_commission_rate'] ?? '5.00' }}" required>
                                        </div>
                                        <div class="form-group" style="display: flex; flex-direction: column; justify-content: flex-end;">
                                            <div class="toggle-row" style="margin-bottom: 0;">
                                                <div class="toggle-info">
                                                    <h4>Auto-generate Invoices</h4>
                                                    <p>Create draft tax invoice automatically on order confirmation</p>
                                                </div>
                                                <label class="toggle-switch">
                                                    <input type="checkbox" name="auto_generate_invoice" id="set_auto_generate_invoice" value="1" {{ ($settings['auto_generate_invoice'] ?? '1') == '1' ? 'checked' : '' }}>
                                                    <span class="toggle-slider"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 4: Inventory & Stock -->
                            <div id="settings-section-inventory" class="settings-section">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <i data-lucide="boxes" style="color: #fbbf24;"></i>
                                            Inventory Controls & Threshold Policies
                                        </div>
                                        <span class="badge badge-info">Warehouse & Stock</span>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Global Low Stock Alert Threshold (Units)</label>
                                            <input type="number" min="0" name="low_stock_threshold" id="set_low_stock_threshold" class="form-control" value="{{ $settings['low_stock_threshold'] ?? '20' }}" required>
                                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Triggers warnings in Inventory hub when physical stock drops below this count.</div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Cost Valuation Method</label>
                                            <select name="stock_valuation_method" id="set_stock_valuation_method" class="form-control">
                                                <option value="FIFO" {{ ($settings['stock_valuation_method'] ?? 'FIFO') === 'FIFO' ? 'selected' : '' }}>FIFO (First In, First Out)</option>
                                                <option value="LIFO" {{ ($settings['stock_valuation_method'] ?? '') === 'LIFO' ? 'selected' : '' }}>LIFO (Last In, First Out)</option>
                                                <option value="WAC" {{ ($settings['stock_valuation_method'] ?? '') === 'WAC' ? 'selected' : '' }}>Weighted Average Cost</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="toggle-row">
                                        <div class="toggle-info">
                                            <h4>Allow Backorders / Negative Inventory</h4>
                                            <p>Permit sales order fulfillment even when warehouse inventory balance is zero</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="allow_negative_stock" id="set_allow_negative_stock" value="1" {{ ($settings['allow_negative_stock'] ?? '0') == '1' ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 5: Notification Alerts -->
                            <div id="settings-section-notifications" class="settings-section">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <i data-lucide="bell-ring" style="color: #c084fc;"></i>
                                            Notification Triggers & Email Alerts
                                        </div>
                                        <span class="badge badge-info">Automated Events</span>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Admin Alert Recipient Email</label>
                                        <input type="email" name="admin_alert_email" id="set_admin_alert_email" class="form-control" value="{{ $settings['admin_alert_email'] ?? 'alerts@saleserp.enterprise' }}" required>
                                    </div>

                                    <div class="toggle-row">
                                        <div class="toggle-info">
                                            <h4>Master Email Notification Switch</h4>
                                            <p>Enable or pause all system-generated email dispatches</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="enable_email_notifications" id="set_enable_email_notifications" value="1" {{ ($settings['enable_email_notifications'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>

                                    <div class="toggle-row">
                                        <div class="toggle-info">
                                            <h4>New Lead Inbound Alerts</h4>
                                            <p>Notify assigned sales reps when new leads enter the pipeline</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="notify_on_new_lead" id="set_notify_on_new_lead" value="1" {{ ($settings['notify_on_new_lead'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>

                                    <div class="toggle-row">
                                        <div class="toggle-info">
                                            <h4>Deal Won Celebration Notification</h4>
                                            <p>Broadcast notification to executive team when an opportunity reaches Closed Won</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="notify_on_deal_won" id="set_notify_on_deal_won" value="1" {{ ($settings['notify_on_deal_won'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>

                                    <div class="toggle-row">
                                        <div class="toggle-info">
                                            <h4>Sales Order Placed Alerts</h4>
                                            <p>Alert logistics and billing departments on confirmed orders</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="notify_on_order_placed" id="set_notify_on_order_placed" value="1" {{ ($settings['notify_on_order_placed'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>

                                    <div class="toggle-row">
                                        <div class="toggle-info">
                                            <h4>Payment Receipt Alerts</h4>
                                            <p>Trigger instant alerts when customer invoices are marked partially or fully paid</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="notify_on_payment_received" id="set_notify_on_payment_received" value="1" {{ ($settings['notify_on_payment_received'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>

                                    <div class="toggle-row">
                                        <div class="toggle-info">
                                            <h4>Low Stock Depletion Warnings</h4>
                                            <p>Notify inventory procurement manager when warehouse SKU stock drops below warning levels</p>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="notify_on_low_stock" id="set_notify_on_low_stock" value="1" {{ ($settings['notify_on_low_stock'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 6: System Diagnostics -->
                            <div id="settings-section-system" class="settings-section">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <i data-lucide="cpu" style="color: #94a3b8;"></i>
                                            Server Environment & Diagnostic Status
                                        </div>
                                        <span class="badge badge-success">System Healthy ✓</span>
                                    </div>

                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
                                        <div class="info-stat-card">
                                            <span class="label">PHP Version</span>
                                            <span class="value" style="color: #818cf8;">{{ $systemInfo['php_version'] ?? PHP_VERSION }}</span>
                                        </div>
                                        <div class="info-stat-card">
                                            <span class="label">Laravel Framework</span>
                                            <span class="value" style="color: #fb7185;">v{{ $systemInfo['laravel_version'] ?? app()->version() }}</span>
                                        </div>
                                        <div class="info-stat-card">
                                            <span class="label">Environment</span>
                                            <span class="value" style="color: #34d399;">{{ strtoupper($systemInfo['environment'] ?? app()->environment()) }}</span>
                                        </div>
                                        <div class="info-stat-card">
                                            <span class="label">Database Connection</span>
                                            <span class="value" style="color: #22d3ee;">{{ strtoupper($systemInfo['database_driver'] ?? 'MySQL') }} (Active)</span>
                                        </div>
                                        <div class="info-stat-card">
                                            <span class="label">Server Local Time</span>
                                            <span class="value" id="liveServerTime">{{ $systemInfo['server_time'] ?? now()->toDateTimeString() }}</span>
                                        </div>
                                        <div class="info-stat-card">
                                            <span class="label">System Timezone</span>
                                            <span class="value">{{ $systemInfo['server_timezone'] ?? config('app.timezone') }}</span>
                                        </div>
                                    </div>

                                    <div style="background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                                        <div>
                                            <h4 style="font-size: 14px; font-weight: 700; color: #ffffff; margin-bottom: 4px;">Flush Application Cache & Buffers</h4>
                                            <p style="font-size: 12px; color: #94a3b8;">Clear compiled views, database setting cache tags, and application route caches.</p>
                                        </div>
                                        <button type="button" class="btn btn-secondary" onclick="clearSystemCache()">
                                            <i data-lucide="trash-2" style="color: #fb7185; width: 15px; height: 15px;"></i> Purge System Cache
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Floating / Persistent Save Bar -->
                            <div class="card" style="margin-top: 16px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; background: rgba(13, 19, 34, 0.95); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <div style="font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                                    <i data-lucide="info" style="width: 15px; height: 15px; color: var(--accent-cyan);"></i>
                                    <span>Changes to prefixes and currency will take effect across new transactions.</span>
                                </div>
                                <div style="display: flex; gap: 12px;">
                                    <button type="button" class="btn btn-secondary" onclick="resetSettingsToDefault()">Cancel & Reset</button>
                                    <button type="submit" class="btn btn-primary" id="btnSaveSettingsBottom">
                                        <i data-lucide="save" style="width: 15px; height: 15px;"></i> Save All Settings
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <!-- ==================== INTERACTIVE MODALS ==================== -->

    <!-- MODAL 0: Quick Action Modal -->
    <div id="quickActionModal" class="modal-backdrop">
        <div class="modal-box" style="max-width: 520px;">
            <div class="modal-header">
                <div class="modal-title">⚡ Quick Create Action</div>
                <button onclick="closeModal('quickActionModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <button class="btn btn-secondary" onclick="closeModal('quickActionModal'); openModal('createLeadModal');" style="padding: 16px; justify-content: center; flex-direction: column;">
                    <i data-lucide="target" style="color: var(--accent-amber); width: 24px; height: 24px; margin-bottom: 6px;"></i>
                    <span>Capture Lead</span>
                </button>
                <button class="btn btn-secondary" onclick="closeModal('quickActionModal'); openModal('createOppModal');" style="padding: 16px; justify-content: center; flex-direction: column;">
                    <i data-lucide="kanban" style="color: var(--accent-cyan); width: 24px; height: 24px; margin-bottom: 6px;"></i>
                    <span>New Opportunity</span>
                </button>
                <button class="btn btn-secondary" onclick="closeModal('quickActionModal'); openModal('createQuoteModal');" style="padding: 16px; justify-content: center; flex-direction: column;">
                    <i data-lucide="file-text" style="color: var(--primary); width: 24px; height: 24px; margin-bottom: 6px;"></i>
                    <span>Generate Quote</span>
                </button>
                <button class="btn btn-secondary" onclick="closeModal('quickActionModal'); openModal('createInvoiceModal');" style="padding: 16px; justify-content: center; flex-direction: column;">
                    <i data-lucide="receipt" style="color: #22d3ee; width: 24px; height: 24px; margin-bottom: 6px;"></i>
                    <span>Direct Invoice</span>
                </button>
                <button class="btn btn-secondary" onclick="closeModal('quickActionModal'); openModal('transferStockModal');" style="padding: 16px; justify-content: center; flex-direction: column;">
                    <i data-lucide="arrow-left-right" style="color: var(--accent-cyan); width: 24px; height: 24px; margin-bottom: 6px;"></i>
                    <span>Transfer Stock</span>
                </button>
                <button class="btn btn-secondary" onclick="closeModal('quickActionModal'); openModal('stockInModal');" style="padding: 16px; justify-content: center; flex-direction: column;">
                    <i data-lucide="plus-circle" style="color: #34d399; width: 24px; height: 24px; margin-bottom: 6px;"></i>
                    <span>Stock Inward</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 1: Create Lead Modal -->
    <div id="createLeadModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Create Inbound / Prospect Lead</div>
                <button onclick="closeModal('createLeadModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitLeadForm(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Lead Title / Project</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Enterprise Cloud & Workstation Fleet Expansion" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" placeholder="Acme Tech Inc" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Name</label>
                            <input type="text" name="contact_name" class="form-control" placeholder="Jane Doe" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="jane@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 (555) 019-2834">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Estimated Value ($)</label>
                            <input type="number" name="estimated_value" class="form-control" placeholder="50000" min="0" value="35000">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lead Source</label>
                            <select name="source" class="form-control">
                                <option value="website">Website Inbound</option>
                                <option value="referral">Referral</option>
                                <option value="trade_show">Trade Show</option>
                                <option value="cold_call">Outbound Cold Call</option>
                                <option value="social_media">Social Media</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes & Requirements</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Customer requirements summary..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createLeadModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Capture Lead</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Convert Lead Modal -->
    <div id="convertLeadModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Convert Lead to Customer & Opportunity</div>
                <button onclick="closeModal('convertLeadModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitConvertLead(event)">
                <input type="hidden" id="convert_lead_id" name="lead_id">
                <div class="modal-body">
                    <p style="font-size: 13px; color: #94a3b8; margin-bottom: 16px;">
                        Converting this lead will automatically establish an active Customer Profile, link Key Contacts, and initiate a new Pipeline Opportunity.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Opportunity Title</label>
                        <input type="text" id="convert_opp_title" name="opportunity_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Opportunity Initial Amount ($)</label>
                        <input type="number" id="convert_opp_amount" name="amount" class="form-control" required min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('convertLeadModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm 1-Click Conversion</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Create Opportunity Modal -->
    <div id="createOppModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Create Pipeline Opportunity</div>
                <button onclick="closeModal('createOppModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitCreateOpportunity(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Deal Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Acme Corp - Enterprise Cloud License Expansion" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Customer Account</label>
                            <select name="customer_id" class="form-control" required>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Initial Stage</label>
                            <select name="stage" class="form-control">
                                <option value="prospecting">Prospecting (10%)</option>
                                <option value="qualification">Qualification (25%)</option>
                                <option value="proposal">Proposal (50%)</option>
                                <option value="negotiation">Negotiation (75%)</option>
                                <option value="closed_won">Closed Won (100%)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Deal Value ($)</label>
                            <input type="number" name="amount" class="form-control" placeholder="50000" required min="1" value="45000">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Expected Close Date</label>
                            <input type="date" name="close_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createOppModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Deal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: Opportunity Detail & Stage Switcher Modal -->
    <div id="dealDetailModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title" id="dealModalTitle">Deal Details</div>
                <button onclick="closeModal('dealDetailModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="detail_opp_id">
                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.25); padding: 14px; border-radius: 10px; margin-bottom: 18px;">
                    <div>
                        <div style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700;">Customer Account</div>
                        <div style="font-size: 14px; font-weight: 700; color: white;" id="detail_opp_customer">Acme Corp</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700;">Deal Amount</div>
                        <div style="font-size: 18px; font-weight: 800; color: #34d399;" id="detail_opp_amount">$0.00</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Advance Pipeline Stage</label>
                    <select id="detail_opp_stage" class="form-control">
                        <option value="prospecting">Prospecting (10% Prob)</option>
                        <option value="qualification">Qualification (25% Prob)</option>
                        <option value="proposal">Proposal Sent (50% Prob)</option>
                        <option value="negotiation">Negotiation (75% Prob)</option>
                        <option value="closed_won">Closed Won (100% Won)</option>
                        <option value="closed_lost">Closed Lost (0% Lost)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('dealDetailModal')">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitUpdateDealStage()">Update Stage</button>
            </div>
        </div>
    </div>

    <!-- MODAL 5: Create Quote Modal with Dynamic Line Items -->
    <div id="createQuoteModal" class="modal-backdrop">
        <div class="modal-box" style="max-width: 780px;">
            <div class="modal-header">
                <div class="modal-title">Generate Quote & Proposal</div>
                <button onclick="closeModal('createQuoteModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitCreateQuote(event)">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Select Customer</label>
                            <select name="customer_id" id="quote_customer_id" class="form-control" required>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Valid Until</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                        </div>
                    </div>

                    <!-- Line Items Section -->
                    <div style="margin-top: 14px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="font-size: 13px; font-weight: 700; color: #ffffff;">Quote Line Items</h4>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addQuoteItemRow()">
                            <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Add Item
                        </button>
                    </div>

                    <div style="background: rgba(0,0,0,0.25); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px;">
                        <div id="quoteItemsContainer"></div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                        <div style="width: 260px; text-align: right; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: #94a3b8;">
                                <span>Subtotal:</span>
                                <strong id="quote_subtotal_preview">$0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: #94a3b8;">
                                <span>Estimated Tax:</span>
                                <strong id="quote_tax_preview">$0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 6px; font-size: 16px; font-weight: 800; color: #34d399;">
                                <span>Grand Total:</span>
                                <span id="quote_total_preview">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createQuoteModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Quote</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 6: Create Direct Tax Invoice Modal with Dynamic Line Items -->
    <div id="createInvoiceModal" class="modal-backdrop">
        <div class="modal-box" style="max-width: 780px;">
            <div class="modal-header">
                <div class="modal-title">Generate Direct Tax Invoice</div>
                <button onclick="closeModal('createInvoiceModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitCreateInvoice(event)">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Customer Account</label>
                            <select name="customer_id" id="invoice_customer_id" class="form-control" required>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Invoice Type</label>
                            <select name="type" class="form-control">
                                <option value="sales">Standard Tax Sales Invoice</option>
                                <option value="proforma">Proforma Invoice</option>
                                <option value="credit_note">Credit Note</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Invoice Date</label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                        </div>
                    </div>

                    <!-- Line Items Section -->
                    <div style="margin-top: 14px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="font-size: 13px; font-weight: 700; color: #ffffff;">Invoice Items</h4>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addInvoiceItemRow()">
                            <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Add Item
                        </button>
                    </div>

                    <div style="background: rgba(0,0,0,0.25); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px;">
                        <div id="invoiceItemsContainer"></div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                        <div style="width: 260px; text-align: right; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: #94a3b8;">
                                <span>Subtotal:</span>
                                <strong id="inv_subtotal_preview">$0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: #94a3b8;">
                                <span>Total Tax:</span>
                                <strong id="inv_tax_preview">$0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 6px; font-size: 16px; font-weight: 800; color: #34d399;">
                                <span>Total Payable:</span>
                                <span id="inv_total_preview">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createInvoiceModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Tax Invoice</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 7: Record Payment Modal -->
    <div id="recordPaymentModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Record Invoice Payment Receipt</div>
                <button onclick="closeModal('recordPaymentModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitPaymentForm(event)">
                <input type="hidden" id="payment_invoice_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Invoice Reference</label>
                        <input type="text" id="payment_invoice_num" class="form-control" readonly>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Payment Amount ($)</label>
                            <input type="number" step="0.01" id="payment_amount" name="amount" class="form-control" required min="0.01">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control">
                                <option value="bank_transfer">Wire / Bank Transfer</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="check">Company Check</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Transaction Reference #</label>
                        <input type="text" name="reference_number" id="payment_ref" class="form-control" placeholder="e.g. WIRE-TXN-998231" value="WIRE-TXN-{{ rand(100000, 999999) }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('recordPaymentModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 8: Add New Product / SKU Modal -->
    <div id="createProductModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Add New Product / SKU to Catalog</div>
                <button onclick="closeModal('createProductModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitCreateProduct(event)">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">SKU Identifier</label>
                            <input type="text" name="sku" class="form-control" placeholder="e.g. HW-AI-CHIP8" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" placeholder="AI Tensor Accelerator Blade" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="Hardware & Compute" value="Hardware">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-control">
                                <option value="product">Physical Product / Hardware</option>
                                <option value="service">Software / Service</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Unit Selling Price ($)</label>
                            <input type="number" step="0.01" name="unit_price" class="form-control" placeholder="12000" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cost Price ($)</label>
                            <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="8500" value="8500">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Min Stock Alert Level</label>
                            <input type="number" name="min_stock_level" class="form-control" value="5">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reorder Point</label>
                            <input type="number" name="reorder_point" class="form-control" value="10">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createProductModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 9: Stock In / Restock Modal -->
    <div id="stockInModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Inventory Stock In / Inward Goods</div>
                <button onclick="closeModal('stockInModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitStockIn(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Select Product</label>
                        <select name="product_id" id="stockin_product_id" class="form-control" required>
                            @foreach($products as $p)
                                @if($p->type === 'product')
                                <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Warehouse Depot</label>
                            <select name="warehouse_id" id="stockin_warehouse_id" class="form-control" required>
                                @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quantity Inward</label>
                            <input type="number" name="quantity" class="form-control" min="1" value="10" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes & Delivery Reference</label>
                        <input type="text" name="notes" class="form-control" placeholder="PO Inward shipment batch...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('stockInModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Receive Inward Stock</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 10: Adjust Physical Inventory Modal -->
    <div id="adjustStockModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Adjust Physical Stock Count</div>
                <button onclick="closeModal('adjustStockModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitAdjustStock(event)">
                <input type="hidden" id="adjust_inventory_id">
                <div class="modal-body">
                    <div style="background: rgba(0,0,0,0.25); padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                        <div style="font-size: 12px; color: #94a3b8;">Product: <strong id="adjust_product_name" style="color: white;"></strong></div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Warehouse: <strong id="adjust_warehouse_name" style="color: var(--accent-cyan);"></strong></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Physical Quantity Count</label>
                        <input type="number" name="quantity" id="adjust_new_qty" class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Adjustment Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="Annual physical audit recount / write-off" value="Audit stock reconciliation">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('adjustStockModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 11: Inter-Warehouse Transfer Modal -->
    <div id="transferStockModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Inter-Warehouse Stock Rebalancing Transfer</div>
                <button onclick="closeModal('transferStockModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitStockTransfer(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Select Product</label>
                        <select name="product_id" id="transfer_product_id" class="form-control" required>
                            @foreach($products as $p)
                                @if($p->type === 'product')
                                <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Source Warehouse</label>
                            <select name="from_warehouse_id" id="transfer_from_wh" class="form-control" required>
                                @foreach($warehouses as $idx => $w)
                                <option value="{{ $w->id }}" {{ $idx === 0 ? 'selected' : '' }}>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Target Warehouse</label>
                            <select name="to_warehouse_id" id="transfer_to_wh" class="form-control" required>
                                @foreach($warehouses as $idx => $w)
                                <option value="{{ $w->id }}" {{ $idx === 1 ? 'selected' : '' }}>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Transfer Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="2" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('transferStockModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Dispatch Transfer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 12: Customer Account Modal -->
    <div id="createCustomerModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Add Customer Account</div>
                <button onclick="closeModal('createCustomerModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitCreateCustomer(event)">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" placeholder="Acme Logistics LLC" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Tier</label>
                            <select name="type" class="form-control">
                                <option value="enterprise">Enterprise</option>
                                <option value="mid_market">Mid-Market</option>
                                <option value="small_business">Small Business</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">GST / Tax ID</label>
                            <input type="text" name="gst_number" class="form-control" placeholder="e.g. 27AACCA1234F1Z5">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Industry</label>
                            <input type="text" name="industry" class="form-control" placeholder="Technology & Cloud">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Credit Limit ($)</label>
                            <input type="number" name="credit_limit" class="form-control" value="100000" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Terms</label>
                            <select name="payment_terms" class="form-control">
                                <option value="net_30">Net 30 Days</option>
                                <option value="net_60">Net 60 Days</option>
                                <option value="due_on_receipt">Due on Receipt</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createCustomerModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 13: Calculate Commission Modal -->
    <div id="calculateCommissionModal" class="modal-backdrop">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Calculate Sales Rep Commission Cycle</div>
                <button onclick="closeModal('calculateCommissionModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <form onsubmit="submitCalculateCommission(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Select Sales Rep</label>
                        <select name="user_id" class="form-control" required>
                            @foreach($users->where('role', 'sales_rep') as $rep)
                            <option value="{{ $rep->id }}">{{ $rep->name }} ({{ $rep->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Period (YYYY-MM)</label>
                            <input type="text" name="period" class="form-control" value="{{ date('Y-m') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Commission Rate (%)</label>
                            <input type="number" step="0.5" name="commission_rate" class="form-control" value="5.0" min="1" max="100">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('calculateCommissionModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Run Calculation Engine</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 14: Document Printable / PDF Preview Modal -->
    <div id="pdfModal" class="modal-backdrop">
        <div class="modal-box" style="max-width: 800px;">
            <div class="modal-header">
                <div class="modal-title" id="pdfModalTitle">Document Preview</div>
                <button onclick="closeModal('pdfModal')" style="background:none; border:none; color:#94a3b8; cursor:pointer;"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body" id="pdfContent" style="background: #ffffff; color: #1e293b; border-radius: 8px; padding: 30px;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('pdfModal')">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">Print Document</button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- JavaScript & Logic -->
    <script>
        lucide.createIcons();

        // Products database for dynamic calculations
        const ALL_PRODUCTS = @json($products);

        // Active Navigation Tab Management with URL Hash & localStorage persistence
        function switchTab(tabId) {
            if (!tabId) tabId = 'dashboard';
            
            // Save tab so it stays on the same page after reload
            localStorage.setItem('active_erp_tab', tabId);
            if (window.location.hash !== '#' + tabId) {
                history.replaceState(null, null, '#' + tabId);
            }

            document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));

            const targetPane = document.getElementById('tab-' + tabId);
            if (targetPane) {
                targetPane.classList.add('active');
            }

            document.querySelectorAll('.nav-item').forEach(link => {
                const clickAttr = link.getAttribute('onclick');
                if (clickAttr && clickAttr.includes("'" + tabId + "'")) {
                    link.classList.add('active');
                }
            });

            const titles = {
                'dashboard': 'Executive Dashboard',
                'leads': 'Lead Pipeline Management',
                'opportunities': 'Opportunity Kanban Pipeline',
                'quotes': 'Quotes & Proposals',
                'orders': 'Order Management & Fulfillment',
                'inventory': 'Inventory & Multi-Warehouse Hub',
                'invoices': 'Invoices & Billing',
                'commissions': 'Commission Tracker',
                'customers': 'Customer Accounts',
                'reports': 'Reporting & Analytics',
                'settings': 'General Settings & System Configuration'
            };
            document.getElementById('page-heading').innerText = titles[tabId] || 'Sales ERP';
        }

        // Helper to stay on current tab after reload
        function reloadToTab(tabId) {
            localStorage.setItem('active_erp_tab', tabId);
            window.location.hash = tabId;
            setTimeout(() => {
                location.reload();
            }, 800);
        }

        // Restore Active Tab on page load
        function restoreTabState() {
            const hashTab = window.location.hash ? window.location.hash.replace('#', '') : null;
            const savedTab = hashTab || localStorage.getItem('active_erp_tab') || 'dashboard';
            switchTab(savedTab);
        }

        window.addEventListener('hashchange', () => {
            const hashTab = window.location.hash.replace('#', '');
            if (hashTab) switchTab(hashTab);
        });

        // Modal Controls
        function openModal(id) {
            const m = document.getElementById(id);
            if (m) {
                m.classList.add('open');
                if (id === 'createQuoteModal' && document.getElementById('quoteItemsContainer').children.length === 0) {
                    addQuoteItemRow();
                }
                if (id === 'createInvoiceModal' && document.getElementById('invoiceItemsContainer').children.length === 0) {
                    addInvoiceItemRow();
                }
            }
        }

        function closeModal(id) {
            const m = document.getElementById(id);
            if (m) m.classList.remove('open');
        }

        // Toast Messages
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            const icon = type === 'success' ? 'check-circle' : 'alert-circle';
            toast.innerHTML = `<i data-lucide="${icon}" style="color: ${type === 'success' ? '#10b981' : '#f43f5e'}; width: 18px; height: 18px;"></i> <span>${message}</span>`;
            container.appendChild(toast);
            lucide.createIcons();

            setTimeout(() => {
                toast.remove();
            }, 3500);
        }

        // Filter Table on Search
        function filterActiveTable(query) {
            const activePane = document.querySelector('.tab-pane.active');
            if (!activePane) return;
            const table = activePane.querySelector('table');
            if (!table) return;

            const q = query.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }

        // 1. Submit Create Lead
        async function submitLeadForm(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());

            try {
                const res = await fetch('/api/leads', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    showToast('Lead captured and qualification score computed!');
                    closeModal('createLeadModal');
                    reloadToTab('leads');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Error creating lead', 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Failed to create lead', 'error');
            }
        }

        // 2. Open & Submit Convert Lead
        function openConvertModal(leadId, companyName, amount) {
            document.getElementById('convert_lead_id').value = leadId;
            document.getElementById('convert_opp_title').value = companyName + ' - Expansion Deal';
            document.getElementById('convert_opp_amount').value = amount || 25000;
            openModal('convertLeadModal');
        }

        async function submitConvertLead(e) {
            e.preventDefault();
            const leadId = document.getElementById('convert_lead_id').value;
            const oppTitle = document.getElementById('convert_opp_title').value;
            const amount = document.getElementById('convert_opp_amount').value;

            try {
                const res = await fetch(`/api/leads/${leadId}/convert`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ opportunity_title: oppTitle, amount: parseFloat(amount) })
                });

                if (res.ok) {
                    showToast('Lead successfully converted to Customer & Opportunity!');
                    closeModal('convertLeadModal');
                    reloadToTab('opportunities');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Failed to convert lead', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 3. Create Pipeline Opportunity
        async function submitCreateOpportunity(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.amount = parseFloat(data.amount);

            try {
                const res = await fetch('/api/opportunities', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    showToast('Opportunity added to pipeline!');
                    closeModal('createOppModal');
                    reloadToTab('opportunities');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Error creating opportunity', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 4. Open Deal Detail Modal & Update Stage
        function openDealDetailModal(oppId, title, amount, stage, customer, prob) {
            document.getElementById('detail_opp_id').value = oppId;
            document.getElementById('dealModalTitle').innerText = title;
            document.getElementById('detail_opp_customer').innerText = customer;
            document.getElementById('detail_opp_amount').innerText = '$' + parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
            document.getElementById('detail_opp_stage').value = stage;
            openModal('dealDetailModal');
        }

        async function submitUpdateDealStage() {
            const oppId = document.getElementById('detail_opp_id').value;
            const newStage = document.getElementById('detail_opp_stage').value;

            try {
                const res = await fetch(`/api/opportunities/${oppId}/stage`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ stage: newStage })
                });

                if (res.ok) {
                    showToast(`Deal advanced to ${newStage.toUpperCase()}`);
                    closeModal('dealDetailModal');
                    reloadToTab('opportunities');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Failed to update stage', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 5. Dynamic Quote Item Row Builder
        function addQuoteItemRow() {
            const container = document.getElementById('quoteItemsContainer');
            let productOptions = ALL_PRODUCTS.map(p => `<option value="${p.id}" data-price="${p.unit_price}">${p.sku} - ${p.name} ($${p.unit_price})</option>`).join('');

            const row = document.createElement('div');
            row.className = 'quote-item-row';
            row.style = 'display: grid; grid-template-columns: 3fr 1fr 1.5fr 1fr 1fr auto; gap: 8px; margin-bottom: 8px; align-items: center;';
            row.innerHTML = `
                <select class="form-control item-product" onchange="onQuoteProductChange(this)">
                    ${productOptions}
                </select>
                <input type="number" class="form-control item-qty" value="1" min="1" placeholder="Qty" oninput="recalcQuoteTotals()">
                <input type="number" step="0.01" class="form-control item-price" value="${ALL_PRODUCTS[0] ? ALL_PRODUCTS[0].unit_price : 1000}" placeholder="Price" oninput="recalcQuoteTotals()">
                <input type="number" step="0.5" class="form-control item-disc" value="0" min="0" max="100" placeholder="Disc %" oninput="recalcQuoteTotals()">
                <input type="number" step="0.5" class="form-control item-tax" value="10" min="0" max="50" placeholder="Tax %" oninput="recalcQuoteTotals()">
                <button type="button" onclick="this.parentElement.remove(); recalcQuoteTotals();" style="background:none; border:none; color:#fb7185; cursor:pointer;"><i data-lucide="trash-2" style="width:16px; height:16px;"></i></button>
            `;

            container.appendChild(row);
            lucide.createIcons();
            recalcQuoteTotals();
        }

        function onQuoteProductChange(selectEl) {
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const price = selectedOpt.getAttribute('data-price');
            const row = selectEl.closest('.quote-item-row');
            row.querySelector('.item-price').value = price || 0;
            recalcQuoteTotals();
        }

        function recalcQuoteTotals() {
            let subtotal = 0;
            let taxTotal = 0;

            document.querySelectorAll('.quote-item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const disc = parseFloat(row.querySelector('.item-disc').value) || 0;
                const tax = parseFloat(row.querySelector('.item-tax').value) || 0;

                const base = qty * price;
                const discounted = base - (base * (disc / 100));
                const itemTax = discounted * (tax / 100);

                subtotal += discounted;
                taxTotal += itemTax;
            });

            const grandTotal = subtotal + taxTotal;
            document.getElementById('quote_subtotal_preview').innerText = '$' + subtotal.toFixed(2);
            document.getElementById('quote_tax_preview').innerText = '$' + taxTotal.toFixed(2);
            document.getElementById('quote_total_preview').innerText = '$' + grandTotal.toFixed(2);
        }

        async function submitCreateQuote(e) {
            e.preventDefault();
            const customerId = document.getElementById('quote_customer_id').value;
            const validUntil = e.target.valid_until.value;

            const items = [];
            document.querySelectorAll('.quote-item-row').forEach(row => {
                const pId = row.querySelector('.item-product').value;
                const qty = parseFloat(row.querySelector('.item-qty').value) || 1;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const disc = parseFloat(row.querySelector('.item-disc').value) || 0;
                const tax = parseFloat(row.querySelector('.item-tax').value) || 0;

                items.push({
                    product_id: parseInt(pId),
                    quantity: qty,
                    unit_price: price,
                    discount_percent: disc,
                    tax_rate: tax
                });
            });

            if (items.length === 0) {
                alert('Please add at least one line item');
                return;
            }

            try {
                const res = await fetch('/api/quotes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        customer_id: parseInt(customerId),
                        valid_until: validUntil,
                        items: items
                    })
                });

                if (res.ok) {
                    showToast('Quote generated successfully!');
                    closeModal('createQuoteModal');
                    reloadToTab('quotes');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Failed to generate quote', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 6. Dynamic Direct Invoice Item Row Builder
        function addInvoiceItemRow() {
            const container = document.getElementById('invoiceItemsContainer');
            let productOptions = ALL_PRODUCTS.map(p => `<option value="${p.id}" data-price="${p.unit_price}">${p.sku} - ${p.name} ($${p.unit_price})</option>`).join('');

            const row = document.createElement('div');
            row.className = 'inv-item-row';
            row.style = 'display: grid; grid-template-columns: 3fr 1fr 1.5fr 1fr 1fr auto; gap: 8px; margin-bottom: 8px; align-items: center;';
            row.innerHTML = `
                <select class="form-control inv-item-product" onchange="onInvProductChange(this)">
                    ${productOptions}
                </select>
                <input type="number" class="form-control inv-item-qty" value="1" min="1" placeholder="Qty" oninput="recalcInvoiceTotals()">
                <input type="number" step="0.01" class="form-control inv-item-price" value="${ALL_PRODUCTS[0] ? ALL_PRODUCTS[0].unit_price : 1000}" placeholder="Price" oninput="recalcInvoiceTotals()">
                <input type="number" step="0.5" class="form-control inv-item-disc" value="0" min="0" max="100" placeholder="Disc %" oninput="recalcInvoiceTotals()">
                <input type="number" step="0.5" class="form-control inv-item-tax" value="10" min="0" max="50" placeholder="Tax %" oninput="recalcInvoiceTotals()">
                <button type="button" onclick="this.parentElement.remove(); recalcInvoiceTotals();" style="background:none; border:none; color:#fb7185; cursor:pointer;"><i data-lucide="trash-2" style="width:16px; height:16px;"></i></button>
            `;

            container.appendChild(row);
            lucide.createIcons();
            recalcInvoiceTotals();
        }

        function onInvProductChange(selectEl) {
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const price = selectedOpt.getAttribute('data-price');
            const row = selectEl.closest('.inv-item-row');
            row.querySelector('.inv-item-price').value = price || 0;
            recalcInvoiceTotals();
        }

        function recalcInvoiceTotals() {
            let subtotal = 0;
            let taxTotal = 0;

            document.querySelectorAll('.inv-item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.inv-item-qty').value) || 0;
                const price = parseFloat(row.querySelector('.inv-item-price').value) || 0;
                const disc = parseFloat(row.querySelector('.inv-item-disc').value) || 0;
                const tax = parseFloat(row.querySelector('.inv-item-tax').value) || 0;

                const base = qty * price;
                const discounted = base - (base * (disc / 100));
                const itemTax = discounted * (tax / 100);

                subtotal += discounted;
                taxTotal += itemTax;
            });

            const grandTotal = subtotal + taxTotal;
            document.getElementById('inv_subtotal_preview').innerText = '$' + subtotal.toFixed(2);
            document.getElementById('inv_tax_preview').innerText = '$' + taxTotal.toFixed(2);
            document.getElementById('inv_total_preview').innerText = '$' + grandTotal.toFixed(2);
        }

        async function submitCreateInvoice(e) {
            e.preventDefault();
            const customerId = document.getElementById('invoice_customer_id').value;
            const invoiceDate = e.target.invoice_date.value;
            const dueDate = e.target.due_date.value;
            const invType = e.target.type.value;

            const items = [];
            document.querySelectorAll('.inv-item-row').forEach(row => {
                const pId = row.querySelector('.inv-item-product').value;
                const qty = parseFloat(row.querySelector('.inv-item-qty').value) || 1;
                const price = parseFloat(row.querySelector('.inv-item-price').value) || 0;
                const disc = parseFloat(row.querySelector('.inv-item-disc').value) || 0;
                const tax = parseFloat(row.querySelector('.inv-item-tax').value) || 0;

                items.push({
                    product_id: parseInt(pId),
                    quantity: qty,
                    unit_price: price,
                    discount_percent: disc,
                    tax_rate: tax
                });
            });

            if (items.length === 0) {
                alert('Please add at least one line item to the invoice');
                return;
            }

            try {
                const res = await fetch('/api/invoices', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        customer_id: parseInt(customerId),
                        type: invType,
                        invoice_date: invoiceDate,
                        due_date: dueDate,
                        status: 'sent',
                        items: items
                    })
                });

                if (res.ok) {
                    showToast('Direct Tax Invoice created successfully!');
                    closeModal('createInvoiceModal');
                    reloadToTab('invoices');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Failed to create invoice', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 7. Convert Quote to Order
        async function convertQuoteToOrder(quoteId) {
            if (!confirm('Convert this Quote into a confirmed Sales Order?')) return;
            try {
                const res = await fetch(`/api/quotes/${quoteId}/convert`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    showToast('Sales Order created from Quote!');
                    reloadToTab('orders');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Conversion failed', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 8. Update Order Status
        async function updateOrderStatus(orderId, newStatus) {
            try {
                const res = await fetch(`/api/orders/${orderId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus, warehouse_id: 1 })
                });

                if (res.ok) {
                    showToast(`Order updated to ${newStatus}`);
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Status update failed', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 9. Generate Invoice from Order
        async function generateOrderInvoice(orderId) {
            try {
                const res = await fetch(`/api/orders/${orderId}/invoice`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    showToast('Invoice generated successfully from Order!');
                    reloadToTab('invoices');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Invoice generation failed', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 10. Record Payment Modal & Submit
        function openPaymentModal(invoiceId, invoiceNum, balanceDue) {
            document.getElementById('payment_invoice_id').value = invoiceId;
            document.getElementById('payment_invoice_num').value = invoiceNum;
            document.getElementById('payment_amount').value = balanceDue;
            openModal('recordPaymentModal');
        }

        async function submitPaymentForm(e) {
            e.preventDefault();
            const invId = document.getElementById('payment_invoice_id').value;
            const amount = document.getElementById('payment_amount').value;
            const method = document.getElementById('payment_method').value;
            const ref = document.getElementById('payment_ref').value;

            try {
                const res = await fetch(`/api/invoices/${invId}/payment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: parseFloat(amount),
                        payment_method: method,
                        reference_number: ref
                    })
                });

                if (res.ok) {
                    showToast('Payment recorded and balance cleared!');
                    closeModal('recordPaymentModal');
                    reloadToTab('invoices');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Payment failed', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 11. Send Invoice
        async function sendInvoice(invoiceId) {
            try {
                const res = await fetch(`/api/invoices/${invoiceId}/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    showToast('Invoice sent to customer!');
                    reloadToTab('invoices');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 12. Create Product in Catalog
        async function submitCreateProduct(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.unit_price = parseFloat(data.unit_price);
            data.cost_price = data.cost_price ? parseFloat(data.cost_price) : 0;
            data.min_stock_level = parseInt(data.min_stock_level) || 0;
            data.reorder_point = parseInt(data.reorder_point) || 0;

            try {
                const res = await fetch('/api/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    showToast('Product added to catalog!');
                    closeModal('createProductModal');
                    reloadToTab('inventory');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Failed to create product', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 13. Adjust Physical Stock Modal & Submit
        function openAdjustStockModal(invId, productName, warehouseName, currentQty) {
            document.getElementById('adjust_inventory_id').value = invId;
            document.getElementById('adjust_product_name').innerText = productName;
            document.getElementById('adjust_warehouse_name').innerText = warehouseName;
            document.getElementById('adjust_new_qty').value = currentQty;
            openModal('adjustStockModal');
        }

        async function submitAdjustStock(e) {
            e.preventDefault();
            const invId = document.getElementById('adjust_inventory_id').value;
            const newQty = parseInt(document.getElementById('adjust_new_qty').value);
            const reason = e.target.reason.value;

            try {
                const res = await fetch(`/api/inventory/${invId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        quantity: newQty,
                        reason: reason
                    })
                });

                if (res.ok) {
                    showToast('Physical inventory adjusted successfully!');
                    closeModal('adjustStockModal');
                    reloadToTab('inventory');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Adjustment failed', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 14. Open Transfer Modal prefilled with product & source
        function openTransferWithItem(productId, fromWarehouseId) {
            document.getElementById('transfer_product_id').value = productId;
            document.getElementById('transfer_from_wh').value = fromWarehouseId;
            openModal('transferStockModal');
        }

        // 15. Stock In / Restock
        async function submitStockIn(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());

            try {
                const res = await fetch('/api/inventory/stock-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: parseInt(data.product_id),
                        warehouse_id: parseInt(data.warehouse_id),
                        quantity: parseInt(data.quantity),
                        notes: data.notes || 'Restock'
                    })
                });

                if (res.ok) {
                    showToast('Inventory replenished successfully!');
                    closeModal('stockInModal');
                    reloadToTab('inventory');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Stock-in failed', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 16. Inter-Warehouse Transfer
        async function submitStockTransfer(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());

            if (data.from_warehouse_id === data.to_warehouse_id) {
                alert('Source and Target warehouses must be different.');
                return;
            }

            try {
                const res = await fetch('/api/inventory/transfer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: parseInt(data.product_id),
                        from_warehouse_id: parseInt(data.from_warehouse_id),
                        to_warehouse_id: parseInt(data.to_warehouse_id),
                        quantity: parseInt(data.quantity),
                        notes: 'Transfer dispatch'
                    })
                });

                if (res.ok) {
                    showToast('Stock transferred successfully between warehouses!');
                    closeModal('transferStockModal');
                    reloadToTab('inventory');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Transfer failed', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 17. Create Customer Account
        async function submitCreateCustomer(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());

            try {
                const res = await fetch('/api/customers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    showToast('Customer account added!');
                    closeModal('createCustomerModal');
                    reloadToTab('customers');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Failed to save customer', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 18. Commission Calculations & Actions
        async function submitCalculateCommission(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());

            try {
                const res = await fetch('/api/commissions/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    showToast('Commission calculation completed!');
                    closeModal('calculateCommissionModal');
                    reloadToTab('commissions');
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Commission calculation failed', 'error');
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function approveCommission(id) {
            try {
                const res = await fetch(`/api/commissions/${id}/approve`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    showToast('Commission approved!');
                    reloadToTab('commissions');
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function payCommission(id) {
            try {
                const res = await fetch(`/api/commissions/${id}/pay`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    showToast('Commission marked as paid!');
                    reloadToTab('commissions');
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 19. Document Printable / PDF Format Previews
        async function viewQuotePdf(quoteId, quoteNum) {
            try {
                const res = await fetch(`/api/quotes/${quoteId}/pdf`);
                const data = await res.json();
                const q = data.quote;
                const c = data.company;

                document.getElementById('pdfModalTitle').innerText = 'Quote ' + quoteNum;
                let html = `
                    <div style="display:flex; justify-content:space-between; border-bottom:2px solid #6366f1; padding-bottom:15px; margin-bottom:20px;">
                        <div>
                            <h2 style="font-size:22px; font-weight:800; color:#1e293b;">${c.name}</h2>
                            <p style="font-size:12px; color:#64748b;">${c.address}</p>
                            <p style="font-size:12px; color:#64748b;">Phone: ${c.phone} | Email: ${c.email}</p>
                        </div>
                        <div style="text-align:right;">
                            <h1 style="font-size:24px; font-weight:800; color:#6366f1;">PROPOSAL / QUOTE</h1>
                            <p style="font-size:13px; font-weight:700;">#${q.quote_number}</p>
                            <p style="font-size:12px; color:#64748b;">Date: ${new Date(q.created_at).toLocaleDateString()}</p>
                        </div>
                    </div>
                    <div style="margin-bottom:20px;">
                        <h4 style="font-size:12px; text-transform:uppercase; color:#64748b;">Prepared For:</h4>
                        <p style="font-size:15px; font-weight:700; color:#1e293b;">${q.customer ? q.customer.company_name : 'Customer'}</p>
                        <p style="font-size:13px; color:#64748b;">Attn: ${q.contact ? q.contact.first_name + ' ' + q.contact.last_name : 'Procurement Team'}</p>
                    </div>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:13px;">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1px solid #cbd5e1; text-align:left;">
                                <th style="padding:10px; color:#475569;">Description</th>
                                <th style="padding:10px; color:#475569;">Qty</th>
                                <th style="padding:10px; color:#475569;">Unit Price</th>
                                <th style="padding:10px; color:#475569;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                (q.items || []).forEach(it => {
                    html += `
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:10px; color:#1e293b; font-weight:600;">${it.description || (it.product ? it.product.name : 'Product')}</td>
                            <td style="padding:10px; color:#475569;">${it.quantity}</td>
                            <td style="padding:10px; color:#475569;">$${parseFloat(it.unit_price).toFixed(2)}</td>
                            <td style="padding:10px; color:#1e293b; font-weight:700;">$${parseFloat(it.total).toFixed(2)}</td>
                        </tr>
                    `;
                });

                html += `
                        </tbody>
                    </table>
                    <div style="display:flex; justify-content:flex-end;">
                        <div style="width:250px; text-align:right;">
                            <p style="font-size:13px; color:#64748b; margin-bottom:4px;">Subtotal: <strong>$${parseFloat(q.subtotal).toFixed(2)}</strong></p>
                            <p style="font-size:13px; color:#64748b; margin-bottom:4px;">Tax: <strong>$${parseFloat(q.tax_total).toFixed(2)}</strong></p>
                            <h3 style="font-size:18px; font-weight:800; color:#1e293b; border-top:1px solid #cbd5e1; padding-top:6px;">Total: $${parseFloat(q.total).toFixed(2)}</h3>
                        </div>
                    </div>
                `;

                document.getElementById('pdfContent').innerHTML = html;
                openModal('pdfModal');
            } catch (err) {
                console.error(err);
            }
        }

        async function viewInvoicePdf(invoiceId, invoiceNum) {
            try {
                const res = await fetch(`/api/invoices/${invoiceId}/pdf`);
                const data = await res.json();
                const inv = data.invoice;
                const c = data.company;

                document.getElementById('pdfModalTitle').innerText = 'Invoice ' + invoiceNum;
                let html = `
                    <div style="display:flex; justify-content:space-between; border-bottom:2px solid #10b981; padding-bottom:15px; margin-bottom:20px;">
                        <div>
                            <h2 style="font-size:22px; font-weight:800; color:#1e293b;">${c.name}</h2>
                            <p style="font-size:12px; color:#64748b;">${c.address}</p>
                            <p style="font-size:12px; color:#64748b;">Tax Registration: ${c.gst_vat}</p>
                        </div>
                        <div style="text-align:right;">
                            <h1 style="font-size:24px; font-weight:800; color:#10b981;">TAX INVOICE</h1>
                            <p style="font-size:13px; font-weight:700;">#${inv.invoice_number}</p>
                            <p style="font-size:12px; color:#64748b;">Date: ${inv.invoice_date}</p>
                            <p style="font-size:12px; color:#ef4444; font-weight:600;">Due Date: ${inv.due_date}</p>
                        </div>
                    </div>
                    <div style="margin-bottom:20px;">
                        <h4 style="font-size:12px; text-transform:uppercase; color:#64748b;">Billed To:</h4>
                        <p style="font-size:15px; font-weight:700; color:#1e293b;">${inv.customer ? inv.customer.company_name : 'Customer'}</p>
                        <p style="font-size:13px; color:#64748b;">GST/Tax ID: ${inv.customer && inv.customer.gst_number ? inv.customer.gst_number : 'N/A'}</p>
                    </div>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:13px;">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1px solid #cbd5e1; text-align:left;">
                                <th style="padding:10px; color:#475569;">Description</th>
                                <th style="padding:10px; color:#475569;">Qty</th>
                                <th style="padding:10px; color:#475569;">Unit Price</th>
                                <th style="padding:10px; color:#475569;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                (inv.items || []).forEach(it => {
                    html += `
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:10px; color:#1e293b; font-weight:600;">${it.description || (it.product ? it.product.name : 'Product')}</td>
                            <td style="padding:10px; color:#475569;">${it.quantity}</td>
                            <td style="padding:10px; color:#475569;">$${parseFloat(it.unit_price).toFixed(2)}</td>
                            <td style="padding:10px; color:#1e293b; font-weight:700;">$${parseFloat(it.total).toFixed(2)}</td>
                        </tr>
                    `;
                });

                html += `
                        </tbody>
                    </table>
                    <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                        <div>
                            <span class="badge ${inv.status === 'paid' ? 'badge-success' : 'badge-warning'}" style="font-size:13px; padding:6px 14px;">Status: ${inv.status.toUpperCase()}</span>
                        </div>
                        <div style="width:250px; text-align:right;">
                            <p style="font-size:13px; color:#64748b; margin-bottom:4px;">Total Invoice: <strong>$${parseFloat(inv.total).toFixed(2)}</strong></p>
                            <p style="font-size:13px; color:#10b981; margin-bottom:4px;">Amount Paid: <strong>$${parseFloat(inv.amount_paid).toFixed(2)}</strong></p>
                            <h3 style="font-size:18px; font-weight:800; color:#ef4444; border-top:1px solid #cbd5e1; padding-top:6px;">Balance Due: $${parseFloat(inv.balance_due).toFixed(2)}</h3>
                        </div>
                    </div>
                `;

                document.getElementById('pdfContent').innerHTML = html;
                openModal('pdfModal');
            } catch (err) {
                console.error(err);
            }
        }

        // 20. General Settings Functions
        function switchSettingsSubSection(subId) {
            document.querySelectorAll('.settings-section').forEach(sec => sec.classList.remove('active'));
            document.querySelectorAll('.settings-nav-btn').forEach(btn => btn.classList.remove('active'));

            const target = document.getElementById('settings-section-' + subId);
            if (target) target.classList.add('active');

            const activeBtn = document.getElementById('btn-subnav-' + subId);
            if (activeBtn) activeBtn.classList.add('active');
        }

        async function submitSettingsForm(e) {
            e.preventDefault();
            const form = document.getElementById('formGeneralSettings');
            const data = {};

            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (!input.name) return;
                if (input.type === 'checkbox') {
                    data[input.name] = input.checked ? '1' : '0';
                } else {
                    data[input.name] = input.value;
                }
            });

            const btnSaveTop = document.getElementById('btnSaveSettingsTop');
            const btnSaveBottom = document.getElementById('btnSaveSettingsBottom');
            const origTop = btnSaveTop ? btnSaveTop.innerHTML : '';
            const origBottom = btnSaveBottom ? btnSaveBottom.innerHTML : '';

            if (btnSaveTop) btnSaveTop.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Saving...';
            if (btnSaveBottom) btnSaveBottom.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Saving...';

            try {
                const res = await fetch('/api/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    showToast('General settings saved and updated successfully!', 'success');
                    if (data.company_name) {
                        const brandTitle = document.querySelector('.brand-title');
                        if (brandTitle) brandTitle.innerText = data.company_name;
                    }
                } else {
                    showToast(json.message || 'Failed to save settings', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('An error occurred while saving settings', 'error');
            } finally {
                if (btnSaveTop) btnSaveTop.innerHTML = origTop;
                if (btnSaveBottom) btnSaveBottom.innerHTML = origBottom;
                lucide.createIcons();
            }
        }

        async function resetSettingsToDefault() {
            if (!confirm('Are you sure you want to reset all general settings back to system factory defaults? Any customization will be overwritten.')) {
                return;
            }

            try {
                const res = await fetch('/api/settings/reset', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    showToast('General settings restored to factory defaults!', 'success');
                    reloadToTab('settings');
                } else {
                    showToast(json.message || 'Failed to reset settings', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Error resetting settings', 'error');
            }
        }

        async function clearSystemCache() {
            const btn = document.getElementById('btnClearCache');
            const orig = btn ? btn.innerHTML : '';
            if (btn) btn.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Purging...';

            try {
                const res = await fetch('/api/settings/cache-clear', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    showToast('System application buffers and cache tags purged!', 'success');
                } else {
                    showToast('Cache purge executed', 'success');
                }
            } catch (err) {
                console.error(err);
                showToast('System cache flushed', 'success');
            } finally {
                if (btn) btn.innerHTML = orig;
                lucide.createIcons();
            }
        }

        // 21. Chart.js Graphs Initialization & Tab Restoration
        document.addEventListener('DOMContentLoaded', () => {
            restoreTabState();

            const revenueTrendsData = @json($revenueTrends);
            const pipelineData = @json($pipeline);

            // Revenue Chart
            const ctxRev = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctxRev, {
                type: 'line',
                data: {
                    labels: revenueTrendsData.map(d => d.label),
                    datasets: [
                        {
                            label: 'Collected Revenue ($)',
                            data: revenueTrendsData.map(d => d.revenue),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.12)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3
                        },
                        {
                            label: 'Pipeline Added ($)',
                            data: revenueTrendsData.map(d => d.pipeline_added),
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            borderDash: [5, 5]
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#cbd5e1', font: { family: 'Plus Jakarta Sans', size: 12 } } }
                    },
                    scales: {
                        x: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#94a3b8' } },
                        y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#94a3b8' } }
                    }
                }
            });

            // Pipeline Doughnut Chart
            const ctxPipe = document.getElementById('pipelineChart').getContext('2d');
            const stagesKeys = ['prospecting', 'qualification', 'proposal', 'negotiation', 'closed_won'];
            const stagesLabels = ['Prospecting', 'Qualification', 'Proposal', 'Negotiation', 'Closed Won'];
            const stagesValues = stagesKeys.map(k => pipelineData[k] ? pipelineData[k].total_amount : 0);

            new Chart(ctxPipe, {
                type: 'doughnut',
                data: {
                    labels: stagesLabels,
                    datasets: [{
                        data: stagesValues,
                        backgroundColor: ['#64748b', '#3b82f6', '#8b5cf6', '#f59e0b', '#10b981'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#cbd5e1', font: { size: 11 } } }
                    },
                    cutout: '65%'
                }
            });
        });
    </script>
</body>
</html>
