<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>StockFlow — Administration</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    /* ============================================================
       VARIABLES CSS — Thème Clair & Sombre
    ============================================================ */
    :root {
      --primary:        #0056b3;
      --primary-dark:   #003d80;
      --primary-light:  #e8f0fb;
      --accent:         #00a8e8;
      --success:        #28a745;
      --danger:         #dc3545;
      --warning:        #ffc107;
      --bg-main:        #f0f4f8;
      --bg-card:        #ffffff;
      --bg-sidebar:     #0056b3;
      --text-main:      #1a1a2e;
      --text-muted:     #6c757d;
      --text-sidebar:   #ffffff;
      --border:         #dee2e6;
      --shadow:         0 4px 20px rgba(0,0,0,0.08);
      --shadow-hover:   0 8px 30px rgba(0,86,179,0.18);
      --radius:         12px;
      --radius-sm:      8px;
      --transition:     all 0.3s ease;
      --sidebar-width:  260px;
    }

    [data-theme="dark"] {
      --bg-main:        #0f1117;
      --bg-card:        #1a1d2e;
      --bg-sidebar:     #0d1117;
      --text-main:      #e2e8f0;
      --text-muted:     #8892a4;
      --text-sidebar:   #e2e8f0;
      --border:         #2d3748;
      --shadow:         0 4px 20px rgba(0,0,0,0.4);
      --shadow-hover:   0 8px 30px rgba(0,168,232,0.2);
      --primary-light:  #1a2744;
    }

    /* ============================================================
       RESET & BASE
    ============================================================ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      background: var(--bg-main);
      color: var(--text-main);
      transition: var(--transition);
      min-height: 100vh;
      display: flex;
    }

    /* ============================================================
       SIDEBAR
    ============================================================ */
    .sidebar {
      width: var(--sidebar-width);
      min-height: 100vh;
      background: var(--bg-sidebar);
      display: flex;
      flex-direction: column;
      position: fixed;
      left: 0; top: 0;
      z-index: 1000;
      transition: var(--transition);
      box-shadow: 4px 0 20px rgba(0,0,0,0.15);
    }

    .sidebar-brand {
      padding: 28px 24px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .sidebar-brand .brand-icon {
      width: 44px; height: 44px;
      background: rgba(255,255,255,0.15);
      border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.3rem; color: #fff;
      backdrop-filter: blur(4px);
    }
    .sidebar-brand h1 {
      font-size: 1.4rem; font-weight: 700;
      color: #fff; letter-spacing: 0.5px;
    }
    .sidebar-brand span { font-size: 0.7rem; color: rgba(255,255,255,0.6); display: block; }

    .sidebar-nav { flex: 1; padding: 16px 0; overflow-y: auto; }
    .nav-section-label {
      font-size: 0.65rem; font-weight: 700; letter-spacing: 1.5px;
      text-transform: uppercase; color: rgba(255,255,255,0.4);
      padding: 16px 24px 6px;
    }

    .nav-item {
      display: flex; align-items: center; gap: 14px;
      padding: 13px 24px;
      color: rgba(255,255,255,0.75);
      cursor: pointer;
      border-radius: 0;
      transition: var(--transition);
      position: relative;
      border: none; background: none; width: 100%;
      text-align: left; font-size: 0.9rem; font-weight: 500;
      text-decoration: none;
    }
    .nav-item:hover {
      background: rgba(255,255,255,0.1);
      color: #fff;
      padding-left: 30px;
    }
    .nav-item.active {
      background: rgba(255,255,255,0.15);
      color: #fff;
      border-left: 3px solid #fff;
    }
    .nav-item.active::before {
      content: '';
      position: absolute; right: 0; top: 50%;
      transform: translateY(-50%);
      width: 4px; height: 60%; border-radius: 4px 0 0 4px;
      background: rgba(255,255,255,0.5);
    }
    .nav-item i { width: 20px; text-align: center; font-size: 1rem; }
    .nav-badge {
      margin-left: auto; background: var(--danger);
      color: #fff; font-size: 0.65rem; font-weight: 700;
      padding: 2px 7px; border-radius: 20px; min-width: 20px; text-align: center;
    }

    .nav-item.logout {
      color: rgba(255,100,100,0.85);
      margin-top: 8px;
    }
    .nav-item.logout:hover { background: rgba(220,53,69,0.15); color: #ff6b6b; }

    .sidebar-footer {
      padding: 16px 24px;
      border-top: 1px solid rgba(255,255,255,0.1);
      display: flex; align-items: center; gap: 12px;
    }
    .user-avatar {
      width: 38px; height: 38px; border-radius: 50%;
      background: rgba(255,255,255,0.2);
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem; color: #fff; font-weight: 700;
    }
    .user-info small { color: rgba(255,255,255,0.5); font-size: 0.7rem; display: block; }
    .user-info strong { color: #fff; font-size: 0.85rem; }

    /* ============================================================
       MAIN CONTENT
    ============================================================ */
    .main-content {
      margin-left: var(--sidebar-width);
      flex: 1; min-height: 100vh;
      display: flex; flex-direction: column;
      transition: var(--transition);
    }

    /* TOPBAR */
    .topbar {
      background: var(--bg-card);
      border-bottom: 1px solid var(--border);
      padding: 14px 32px;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 900;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      transition: var(--transition);
    }
    .topbar-left { display: flex; align-items: center; gap: 16px; }
    .menu-toggle {
      display: none; background: none; border: none;
      color: var(--text-main); font-size: 1.3rem; cursor: pointer;
    }
    .page-title h2 { font-size: 1.2rem; font-weight: 700; color: var(--text-main); }
    .page-title p { font-size: 0.78rem; color: var(--text-muted); }
    .breadcrumb-dot { color: var(--text-muted); font-size: 0.75rem; }

    .topbar-right { display: flex; align-items: center; gap: 16px; }

    /* Dark Mode Toggle */
    .theme-toggle {
      width: 50px; height: 26px; border-radius: 20px;
      background: var(--border); border: none; cursor: pointer;
      position: relative; transition: var(--transition);
      flex-shrink: 0;
    }
    .theme-toggle.dark { background: var(--primary); }
    .theme-toggle::after {
      content: '☀️'; font-size: 0.85rem;
      position: absolute; top: 3px; left: 4px;
      width: 20px; height: 20px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      transition: var(--transition);
    }
    .theme-toggle.dark::after { content: '🌙'; left: 26px; }

    .topbar-btn {
      width: 38px; height: 38px; border-radius: 50%;
      background: var(--bg-main); border: 1px solid var(--border);
      color: var(--text-main); cursor: pointer; font-size: 1rem;
      display: flex; align-items: center; justify-content: center;
      transition: var(--transition); position: relative;
    }
    .topbar-btn:hover { background: var(--primary-light); color: var(--primary); }
    .notif-dot {
      position: absolute; top: 4px; right: 4px;
      width: 8px; height: 8px; border-radius: 50%;
      background: var(--danger); border: 2px solid var(--bg-card);
    }

    /* ============================================================
       PAGE SECTIONS
    ============================================================ */
    .page-section { display: none; padding: 32px; animation: fadeIn 0.35s ease; }
    .page-section.active { display: block; }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .section-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
    }
    .section-header h3 { font-size: 1.3rem; font-weight: 700; color: var(--text-main); }
    .section-header p { font-size: 0.82rem; color: var(--text-muted); margin-top: 2px; }

    /* ============================================================
       STAT CARDS
    ============================================================ */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px; margin-bottom: 32px;
    }
    .stat-card {
      background: var(--bg-card);
      border-radius: var(--radius);
      padding: 24px;
      box-shadow: var(--shadow);
      border: 1px solid var(--border);
      display: flex; align-items: flex-start; gap: 16px;
      transition: var(--transition); position: relative; overflow: hidden;
    }
    .stat-card::before {
      content: ''; position: absolute; top: 0; left: 0;
      width: 4px; height: 100%;
      background: var(--card-color, var(--primary));
      border-radius: var(--radius) 0 0 var(--radius);
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-hover); }
    .stat-icon {
      width: 52px; height: 52px; border-radius: var(--radius-sm);
      background: var(--card-color, var(--primary));
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem; color: #fff; flex-shrink: 0;
    }
    .stat-info { flex: 1; }
    .stat-info label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; }
    .stat-info .value { font-size: 2rem; font-weight: 800; color: var(--text-main); line-height: 1.1; margin: 4px 0; }
    .stat-info .delta {
      font-size: 0.76rem; font-weight: 600;
      display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px;
      border-radius: 20px;
    }
    .delta.up   { background: rgba(40,167,69,0.12);  color: var(--success); }
    .delta.down { background: rgba(220,53,69,0.12);  color: var(--danger); }
    .delta.warn { background: rgba(255,193,7,0.12);  color: var(--warning); }

    /* ============================================================
       CARDS & PANELS
    ============================================================ */
    .card {
      background: var(--bg-card);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      overflow: hidden;
      transition: var(--transition);
    }
    .card-header {
      padding: 18px 24px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px;
    }
    .card-header h4 { font-size: 1rem; font-weight: 700; color: var(--text-main); }
    .card-body { padding: 0; }

    /* Search & Filter Bar */
    .table-controls {
      display: flex; align-items: center; gap: 12px;
      padding: 16px 24px; flex-wrap: wrap;
      border-bottom: 1px solid var(--border);
    }
    .search-box {
      display: flex; align-items: center; gap: 10px;
      background: var(--bg-main); border: 1px solid var(--border);
      border-radius: var(--radius-sm); padding: 9px 14px; flex: 1; min-width: 200px;
      transition: var(--transition);
    }
    .search-box:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,86,179,0.1); }
    .search-box i { color: var(--text-muted); font-size: 0.9rem; }
    .search-box input {
      border: none; background: none; outline: none;
      color: var(--text-main); font-size: 0.88rem; width: 100%;
    }
    .search-box input::placeholder { color: var(--text-muted); }

    .filter-select {
      padding: 9px 14px; border: 1px solid var(--border);
      border-radius: var(--radius-sm); background: var(--bg-main);
      color: var(--text-main); font-size: 0.85rem; cursor: pointer;
      outline: none; transition: var(--transition);
    }
    .filter-select:focus { border-color: var(--primary); }

    /* ============================================================
       TABLE
    ============================================================ */
    .table-wrapper { overflow-x: auto; }
    table {
      width: 100%; border-collapse: collapse; font-size: 0.875rem;
    }
    thead tr {
      background: var(--bg-main);
      border-bottom: 2px solid var(--border);
    }
    thead th {
      padding: 13px 16px; text-align: left;
      font-size: 0.72rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.8px;
      color: var(--text-muted); white-space: nowrap;
    }
    tbody tr {
      border-bottom: 1px solid var(--border);
      transition: var(--transition);
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--primary-light); }
    tbody td {
      padding: 14px 16px; color: var(--text-main);
      vertical-align: middle;
    }
    .td-id { font-family: monospace; color: var(--text-muted); font-size: 0.8rem; }
    .td-ref { font-weight: 600; color: var(--primary); font-family: monospace; }
    .td-name { font-weight: 500; }

    /* Stock Badges */
    .badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 10px; border-radius: 20px;
      font-size: 0.75rem; font-weight: 700; white-space: nowrap;
    }
    .badge-success { background: rgba(40,167,69,0.12); color: #1a7a36; }
    .badge-danger  { background: rgba(220,53,69,0.12);  color: #b02a37; }
    .badge-warning { background: rgba(255,193,7,0.12);  color: #856404; }
    .badge-info    { background: rgba(0,168,232,0.12);  color: #0077a8; }
    .badge-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }

    [data-theme="dark"] .badge-success { color: #5cb85c; }
    [data-theme="dark"] .badge-danger  { color: #ff6b6b; }
    [data-theme="dark"] .badge-warning { color: #ffc107; }

    /* Action Buttons */
    .action-btns { display: flex; gap: 6px; }
    .btn-action {
      width: 32px; height: 32px; border-radius: var(--radius-sm);
      border: none; cursor: pointer; font-size: 0.8rem;
      display: flex; align-items: center; justify-content: center;
      transition: var(--transition);
    }
    .btn-action.edit   { background: rgba(0,86,179,0.1);  color: var(--primary); }
    .btn-action.delete { background: rgba(220,53,69,0.1); color: var(--danger); }
    .btn-action.view   { background: rgba(40,167,69,0.1); color: var(--success); }
    .btn-action:hover  { transform: scale(1.15); filter: brightness(1.15); }

    /* ============================================================
       BUTTONS
    ============================================================ */
    .btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 10px 20px; border-radius: var(--radius-sm);
      font-size: 0.875rem; font-weight: 600;
      cursor: pointer; border: none; transition: var(--transition);
      text-decoration: none; white-space: nowrap;
    }
    .btn-primary {
      background: var(--primary); color: #fff;
      box-shadow: 0 4px 12px rgba(0,86,179,0.3);
    }
    .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,86,179,0.4); }
    .btn-secondary { background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border); }
    .btn-secondary:hover { border-color: var(--primary); color: var(--primary); }
    .btn-danger { background: var(--danger); color: #fff; box-shadow: 0 4px 12px rgba(220,53,69,0.3); }
    .btn-danger:hover { background: #b02a37; transform: translateY(-1px); }
    .btn-sm { padding: 7px 14px; font-size: 0.8rem; }
    .btn-outline {
      background: transparent; border: 2px solid var(--primary);
      color: var(--primary);
    }
    .btn-outline:hover { background: var(--primary); color: #fff; }

    /* ============================================================
       MODAL
    ============================================================ */
    .modal-overlay {
      position: fixed; inset: 0;
      background: rgba(0,0,0,0.55);
      backdrop-filter: blur(4px);
      z-index: 2000;
      display: none; align-items: center; justify-content: center;
      padding: 20px;
      animation: fadeIn 0.2s ease;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: var(--bg-card);
      border-radius: var(--radius);
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      width: 100%; max-width: 560px;
      max-height: 90vh; overflow-y: auto;
      animation: slideUp 0.3s ease;
      border: 1px solid var(--border);
    }
    @keyframes slideUp {
      from { opacity: 0; transform: translateY(30px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0)   scale(1); }
    }
    .modal-header {
      padding: 22px 24px 18px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .modal-header h3 { font-size: 1.1rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
    .modal-header h3 i { color: var(--primary); }
    .modal-close {
      width: 34px; height: 34px; border-radius: 50%;
      background: var(--bg-main); border: none; cursor: pointer;
      color: var(--text-muted); font-size: 1rem;
      display: flex; align-items: center; justify-content: center;
      transition: var(--transition);
    }
    .modal-close:hover { background: var(--danger); color: #fff; }
    .modal-body { padding: 24px; }
    .modal-footer {
      padding: 16px 24px;
      border-top: 1px solid var(--border);
      display: flex; justify-content: flex-end; gap: 12px;
    }

    /* FORM ELEMENTS */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group.full { grid-column: 1 / -1; }
    .form-group label { font-size: 0.8rem; font-weight: 600; color: var(--text-main); }
    .form-group label span { color: var(--danger); margin-left: 2px; }
    .form-control {
      padding: 10px 14px; border: 1.5px solid var(--border);
      border-radius: var(--radius-sm); background: var(--bg-main);
      color: var(--text-main); font-size: 0.875rem;
      outline: none; transition: var(--transition); width: 100%;
    }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,86,179,0.12); background: var(--bg-card); }
    .form-control::placeholder { color: var(--text-muted); }
    .form-hint { font-size: 0.72rem; color: var(--text-muted); }

    /* Delete Modal */
    .modal-delete-icon {
      width: 70px; height: 70px; border-radius: 50%;
      background: rgba(220,53,69,0.1);
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem; color: var(--danger);
      margin: 0 auto 16px;
    }
    .modal-delete-text { text-align: center; }
    .modal-delete-text h4 { font-size: 1.1rem; margin-bottom: 8px; }
    .modal-delete-text p { color: var(--text-muted); font-size: 0.875rem; }
    .modal-delete-text strong { color: var(--danger); }

    /* ============================================================
       HISTORIQUE — Timeline simple
    ============================================================ */
    .timeline { padding: 8px 0; }
    .timeline-item {
      display: flex; gap: 16px; padding: 14px 24px;
      border-bottom: 1px solid var(--border); transition: var(--transition);
    }
    .timeline-item:hover { background: var(--primary-light); }
    .timeline-item:last-child { border-bottom: none; }
    .tl-icon {
      width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center; font-size: 0.9rem;
    }
    .tl-icon.in  { background: rgba(40,167,69,0.12);  color: var(--success); }
    .tl-icon.out { background: rgba(220,53,69,0.12);  color: var(--danger); }
    .tl-icon.adj { background: rgba(0,86,179,0.12);   color: var(--primary); }
    .tl-info { flex: 1; }
    .tl-info strong { font-size: 0.88rem; color: var(--text-main); }
    .tl-info p { font-size: 0.78rem; color: var(--text-muted); margin-top: 2px; }
    .tl-meta { text-align: right; flex-shrink: 0; }
    .tl-meta .qty { font-size: 0.9rem; font-weight: 700; }
    .tl-meta .time { font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; }
    .qty.in  { color: var(--success); }
    .qty.out { color: var(--danger); }

    /* ============================================================
       USER MANAGEMENT TABLE
    ============================================================ */
    .user-avatar-sm {
      width: 34px; height: 34px; border-radius: 50%;
      background: var(--primary); color: #fff;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
    }
    .user-cell { display: flex; align-items: center; gap: 10px; }

    /* ============================================================
       PAGINATION
    ============================================================ */
    .pagination {
      display: flex; align-items: center; justify-content: space-between;
      padding: 14px 24px; border-top: 1px solid var(--border);
      flex-wrap: wrap; gap: 10px;
    }
    .pagination-info { font-size: 0.8rem; color: var(--text-muted); }
    .pagination-btns { display: flex; gap: 4px; }
    .page-btn {
      width: 34px; height: 34px; border-radius: var(--radius-sm);
      border: 1px solid var(--border); background: var(--bg-main);
      color: var(--text-main); cursor: pointer; font-size: 0.82rem;
      display: flex; align-items: center; justify-content: center;
      transition: var(--transition);
    }
    .page-btn:hover, .page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }

    /* ============================================================
       EMPTY STATE
    ============================================================ */
    .empty-state {
      text-align: center; padding: 60px 20px; color: var(--text-muted);
    }
    .empty-state i { font-size: 3.5rem; opacity: 0.3; margin-bottom: 16px; display: block; }
    .empty-state h4 { font-size: 1rem; margin-bottom: 8px; color: var(--text-main); }
    .empty-state p { font-size: 0.85rem; }

    /* ============================================================
       TOAST NOTIFICATION
    ============================================================ */
    .toast-container {
      position: fixed; bottom: 24px; right: 24px;
      z-index: 9999; display: flex; flex-direction: column; gap: 10px;
    }
    .toast {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: var(--radius-sm); padding: 14px 18px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.15);
      display: flex; align-items: center; gap: 12px;
      min-width: 280px; max-width: 360px;
      animation: toastIn 0.3s ease;
    }
    @keyframes toastIn { from { opacity:0; transform: translateX(40px); } to { opacity:1; transform: translateX(0); } }
    .toast-icon { font-size: 1.2rem; }
    .toast.success { border-left: 4px solid var(--success); }
    .toast.success .toast-icon { color: var(--success); }
    .toast.error   { border-left: 4px solid var(--danger); }
    .toast.error   .toast-icon { color: var(--danger); }
    .toast-text strong { font-size: 0.875rem; display: block; }
    .toast-text small  { font-size: 0.775rem; color: var(--text-muted); }

    /* ============================================================
       RESPONSIVE — MOBILE
    ============================================================ */
    .sidebar-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.5); z-index: 999;
    }

    @media (max-width: 900px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.open { transform: translateX(0); }
      .sidebar-overlay.show { display: block; }
      .main-content { margin-left: 0; }
      .menu-toggle { display: flex; }
      .form-grid { grid-template-columns: 1fr; }
      .stats-grid { grid-template-columns: 1fr 1fr; }
      .page-section { padding: 20px 16px; }
      .topbar { padding: 12px 16px; }
    }
    @media (max-width: 540px) {
      .stats-grid { grid-template-columns: 1fr; }
      .table-controls { flex-direction: column; }
      .search-box { min-width: 100%; }
    }
  </style>
</head>
<body>

<!-- ================================================================
     SIDEBAR OVERLAY (Mobile)
================================================================ -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ================================================================
     SIDEBAR
================================================================ -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fas fa-boxes-stacked"></i></div>
    <div>
      <h1>StockFlow</h1>
      <span>Panneau d'administration</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Principal</div>

    <button class="nav-item active" onclick="switchSection('dashboard', this)">
      <i class="fas fa-chart-pie"></i> Dashboard
    </button>

    <button class="nav-item" onclick="switchSection('products', this)">
      <i class="fas fa-box"></i> Gestion des Produits
      <!-- PHP: Badge peut afficher le nombre d'alertes stock -->
      <span class="nav-badge" id="alertBadge">3</span>
    </button>

    <button class="nav-item" onclick="switchSection('history', this)">
      <i class="fas fa-clock-rotate-left"></i> Historique des Mouvements
    </button>

    <div class="nav-section-label">Administration</div>

    <button class="nav-item" onclick="switchSection('users', this)">
      <i class="fas fa-users-gear"></i> Gestion Utilisateurs
    </button>

    <div class="nav-section-label">Session</div>

    <!-- PHP: Remplacer href="#" par le lien de déconnexion réel -->
    <a href="#" class="nav-item logout" onclick="handleLogout(event)">
      <i class="fas fa-right-from-bracket"></i> Déconnexion
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-avatar">
      <!-- PHP: Première lettre du prénom admin -->
      A
    </div>
    <div class="user-info">
      <!-- PHP: Afficher le nom de l'admin connecté -->
      <strong>Admin User</strong>
      <small>Administrateur</small>
    </div>
  </div>
</aside>

<!-- ================================================================
     MAIN CONTENT
================================================================ -->
<main class="main-content">

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
      <div class="page-title">
        <h2 id="pageTitle">Dashboard</h2>
        <p>StockFlow <span class="breadcrumb-dot">›</span> <span id="pageBreadcrumb">Vue d'ensemble</span></p>
      </div>
    </div>
    <div class="topbar-right">
      <button class="topbar-btn" title="Notifications">
        <i class="fas fa-bell"></i>
        <span class="notif-dot"></span>
      </button>
      <button class="topbar-btn" title="Rafraîchir" onclick="showToast('Données actualisées', 'Tableau de bord mis à jour', 'success')">
        <i class="fas fa-rotate-right"></i>
      </button>
      <button class="theme-toggle" id="themeToggle" title="Basculer le thème" onclick="toggleTheme()"></button>
    </div>
  </header>

  <!-- ============================================================
       SECTION 1 : DASHBOARD
  ============================================================ -->
  <section class="page-section active" id="sec-dashboard">
    <div class="section-header">
      <div>
        <h3>Vue d'ensemble</h3>
        <p>Statistiques en temps réel de votre inventaire</p>
      </div>
      <button class="btn btn-primary btn-sm" onclick="openModal('modalAddProduct')">
        <i class="fas fa-plus"></i> Nouveau Produit
      </button>
    </div>

    <!-- STAT CARDS -->
    <div class="stats-grid">
      <!-- PHP: Ces valeurs seront calculées côté serveur -->
      <div class="stat-card" style="--card-color: #0056b3;">
        <div class="stat-icon"><i class="fas fa-box"></i></div>
        <div class="stat-info">
          <label>Total Produits</label>
          <!-- PHP: echo $totalProduits; -->
          <div class="value">148</div>
          <span class="delta up"><i class="fas fa-arrow-up"></i> +12 ce mois</span>
        </div>
      </div>

      <div class="stat-card" style="--card-color: #dc3545;">
        <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="stat-info">
          <label>Alertes Stock</label>
          <!-- PHP: echo $alertesStock; -->
          <div class="value">3</div>
          <span class="delta down"><i class="fas fa-arrow-up"></i> +2 vs hier</span>
        </div>
      </div>

      <div class="stat-card" style="--card-color: #28a745;">
        <div class="stat-icon"><i class="fas fa-euro-sign"></i></div>
        <div class="stat-info">
          <label>Chiffre d'Affaires</label>
          <!-- PHP: echo number_format($ca, 2, ',', ' ') . ' €'; -->
          <div class="value">24 580 €</div>
          <span class="delta up"><i class="fas fa-arrow-up"></i> +8.4% ce mois</span>
        </div>
      </div>

      <div class="stat-card" style="--card-color: #00a8e8;">
        <div class="stat-icon"><i class="fas fa-truck-fast"></i></div>
        <div class="stat-info">
          <label>Mouvements Aujourd'hui</label>
          <!-- PHP: echo $mouvementsAujourdhui; -->
          <div class="value">27</div>
          <span class="delta warn"><i class="fas fa-minus"></i> Stable</span>
        </div>
      </div>
    </div>

    <!-- RECENT PRODUCTS TABLE (condensed) -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-exclamation-triangle" style="color:var(--danger)"></i> Produits en Alerte</h4>
        <button class="btn btn-outline btn-sm" onclick="switchSection('products', document.querySelectorAll('.nav-item')[1])">
          Voir tous <i class="fas fa-arrow-right"></i>
        </button>
      </div>
      <div class="card-body">
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Référence</th>
                <th>Nom du Produit</th>
                <th>Catégorie</th>
                <th>Stock Actuel</th>
                <th>Seuil Min.</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody id="alertTableBody">
              <!-- =============================================
                   PHP INJECTION POINT — Produits en alerte
                   foreach($alertes as $produit):
              ================================================ -->
              <tr>
                <td class="td-ref">PRD-001</td>
                <td class="td-name">Câble HDMI 2.0</td>
                <td><span class="badge badge-info">Électronique</span></td>
                <td><strong style="color:var(--danger)">3</strong></td>
                <td>10</td>
                <td><span class="badge badge-danger"><span class="badge-dot"></span>Critique</span></td>
              </tr>
              <tr>
                <td class="td-ref">PRD-007</td>
                <td class="td-name">Papier A4 Ramette</td>
                <td><span class="badge badge-info">Bureautique</span></td>
                <td><strong style="color:var(--warning)">8</strong></td>
                <td>10</td>
                <td><span class="badge badge-warning"><span class="badge-dot"></span>Faible</span></td>
              </tr>
              <tr>
                <td class="td-ref">PRD-012</td>
                <td class="td-name">Souris Optique</td>
                <td><span class="badge badge-info">Informatique</span></td>
                <td><strong style="color:var(--danger)">1</strong></td>
                <td>5</td>
                <td><span class="badge badge-danger"><span class="badge-dot"></span>Critique</span></td>
              </tr>
              <!-- PHP: endforeach; -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================
       SECTION 2 : GESTION DES PRODUITS
  ============================================================ -->
  <section class="page-section" id="sec-products">
    <div class="section-header">
      <div>
        <h3>Gestion des Produits</h3>
        <p>Gérez votre inventaire complet</p>
      </div>
      <button class="btn btn-primary" onclick="openModal('modalAddProduct')">
        <i class="fas fa-plus"></i> Ajouter un Produit
      </button>
    </div>

    <div class="card">
      <!-- Controls -->
      <div class="table-controls">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="searchInput" placeholder="Rechercher par nom, réf, catégorie..." oninput="filterTable()"/>
        </div>
        <select class="filter-select" id="categoryFilter" onchange="filterTable()">
          <option value="">Toutes catégories</option>
          <!-- PHP: foreach($categories as $cat): -->
          <option value="electronique">Électronique</option>
          <option value="informatique">Informatique</option>
          <option value="bureautique">Bureautique</option>
          <option value="mobilier">Mobilier</option>
          <!-- PHP: endforeach; -->
        </select>
        <select class="filter-select" id="stockFilter" onchange="filterTable()">
          <option value="">Tout le stock</option>
          <option value="alert">En alerte</option>
          <option value="ok">Disponible</option>
        </select>
      </div>

      <!-- Table -->
      <div class="card-body">
        <div class="table-wrapper">
          <table id="productsTable">
            <thead>
              <tr>
                <th>#ID</th>
                <th>Référence</th>
                <th>Nom du Produit</th>
                <th>Catégorie</th>
                <th>Stock Actuel</th>
                <th>Seuil Min.</th>
                <th>Prix Unitaire</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="productsTableBody">
              <!-- =============================================
                   PHP INJECTION POINT — Liste complète produits
                   foreach($produits as $produit):
              ================================================ -->
              <tr data-category="electronique" data-stock="ok">
                <td class="td-id">#001</td>
                <td class="td-ref">PRD-001</td>
                <td class="td-name">Câble HDMI 2.0</td>
                <td><span class="badge badge-info">Électronique</span></td>
                <td><strong>3</strong></td>
                <td>10</td>
                <td>12,50 €</td>
                <td><span class="badge badge-danger"><span class="badge-dot"></span>Alerte</span></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-action view" title="Voir" onclick="viewProduct(1)"><i class="fas fa-eye"></i></button>
                    <button class="btn-action edit" title="Modifier" onclick="editProduct(1, 'PRD-001', 'Câble HDMI 2.0', 'electronique', 3, 10, 12.50)"><i class="fas fa-pencil"></i></button>
                    <button class="btn-action delete" title="Supprimer" onclick="confirmDelete(1, 'Câble HDMI 2.0')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <tr data-category="informatique" data-stock="ok">
                <td class="td-id">#002</td>
                <td class="td-ref">PRD-002</td>
                <td class="td-name">Clavier Mécanique RGB</td>
                <td><span class="badge badge-info">Informatique</span></td>
                <td><strong>45</strong></td>
                <td>5</td>
                <td>89,99 €</td>
                <td><span class="badge badge-success"><span class="badge-dot"></span>Disponible</span></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-action view" title="Voir" onclick="viewProduct(2)"><i class="fas fa-eye"></i></button>
                    <button class="btn-action edit" title="Modifier" onclick="editProduct(2, 'PRD-002', 'Clavier Mécanique RGB', 'informatique', 45, 5, 89.99)"><i class="fas fa-pencil"></i></button>
                    <button class="btn-action delete" title="Supprimer" onclick="confirmDelete(2, 'Clavier Mécanique RGB')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <tr data-category="bureautique" data-stock="alert">
                <td class="td-id">#003</td>
                <td class="td-ref">PRD-007</td>
                <td class="td-name">Papier A4 Ramette</td>
                <td><span class="badge badge-info">Bureautique</span></td>
                <td><strong>8</strong></td>
                <td>10</td>
                <td>5,30 €</td>
                <td><span class="badge badge-warning"><span class="badge-dot"></span>Faible</span></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-action view" title="Voir" onclick="viewProduct(3)"><i class="fas fa-eye"></i></button>
                    <button class="btn-action edit" title="Modifier" onclick="editProduct(3, 'PRD-007', 'Papier A4 Ramette', 'bureautique', 8, 10, 5.30)"><i class="fas fa-pencil"></i></button>
                    <button class="btn-action delete" title="Supprimer" onclick="confirmDelete(3, 'Papier A4 Ramette')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <tr data-category="mobilier" data-stock="ok">
                <td class="td-id">#004</td>
                <td class="td-ref">PRD-015</td>
                <td class="td-name">Chaise de Bureau Ergonomique</td>
                <td><span class="badge badge-info">Mobilier</span></td>
                <td><strong>12</strong></td>
                <td>3</td>
                <td>249,00 €</td>
                <td><span class="badge badge-success"><span class="badge-dot"></span>Disponible</span></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-action view" title="Voir" onclick="viewProduct(4)"><i class="fas fa-eye"></i></button>
                    <button class="btn-action edit" title="Modifier" onclick="editProduct(4, 'PRD-015', 'Chaise de Bureau Ergonomique', 'mobilier', 12, 3, 249.00)"><i class="fas fa-pencil"></i></button>
                    <button class="btn-action delete" title="Supprimer" onclick="confirmDelete(4, 'Chaise de Bureau Ergonomique')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <tr data-category="informatique" data-stock="alert">
                <td class="td-id">#005</td>
                <td class="td-ref">PRD-012</td>
                <td class="td-name">Souris Optique Sans Fil</td>
                <td><span class="badge badge-info">Informatique</span></td>
                <td><strong>1</strong></td>
                <td>5</td>
                <td>34,90 €</td>
                <td><span class="badge badge-danger"><span class="badge-dot"></span>Critique</span></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-action view" title="Voir" onclick="viewProduct(5)"><i class="fas fa-eye"></i></button>
                    <button class="btn-action edit" title="Modifier" onclick="editProduct(5, 'PRD-012', 'Souris Optique Sans Fil', 'informatique', 1, 5, 34.90)"><i class="fas fa-pencil"></i></button>
                    <button class="btn-action delete" title="Supprimer" onclick="confirmDelete(5, 'Souris Optique Sans Fil')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <!-- PHP: endforeach; -->
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <span class="pagination-info">
            <!-- PHP: Afficher "Affichage X-Y sur Z résultats" -->
            Affichage 1–5 sur 148 produits
          </span>
          <div class="pagination-btns">
            <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <span style="padding:0 4px;color:var(--text-muted)">…</span>
            <button class="page-btn">30</button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================
       SECTION 3 : HISTORIQUE DES MOUVEMENTS
  ============================================================ -->
  <section class="page-section" id="sec-history">
    <div class="section-header">
      <div>
        <h3>Historique des Mouvements</h3>
        <p>Traçabilité complète de tous les mouvements de stock</p>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-secondary btn-sm"><i class="fas fa-file-export"></i> Exporter CSV</button>
        <button class="btn btn-secondary btn-sm"><i class="fas fa-print"></i> Imprimer</button>
      </div>
    </div>

    <div class="card">
      <div class="table-controls">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Rechercher dans l'historique..."/>
        </div>
        <select class="filter-select">
          <option value="">Tous les types</option>
          <option value="in">Entrées</option>
          <option value="out">Sorties</option>
          <option value="adj">Ajustements</option>
        </select>
        <input type="date" class="filter-select" style="padding:9px 12px;">
        <input type="date" class="filter-select" style="padding:9px 12px;">
      </div>

      <div class="card-body">
        <!-- =============================================
             PHP INJECTION POINT — Historique mouvements
             foreach($mouvements as $mvt):
        ================================================ -->
        <div class="timeline">
          <div class="timeline-item">
            <div class="tl-icon in"><i class="fas fa-arrow-down"></i></div>
            <div class="tl-info">
              <strong>Entrée — Clavier Mécanique RGB</strong>
              <p>Réf: PRD-002 · Opérateur: <strong>Jean Dupont</strong> · Fournisseur: TechSupply</p>
            </div>
            <div class="tl-meta">
              <div class="qty in">+50 unités</div>
              <div class="time">Aujourd'hui, 14:32</div>
            </div>
          </div>
          <div class="timeline-item">
            <div class="tl-icon out"><i class="fas fa-arrow-up"></i></div>
            <div class="tl-info">
              <strong>Sortie — Papier A4 Ramette</strong>
              <p>Réf: PRD-007 · Opérateur: <strong>Marie Martin</strong> · Client: Bureau Dept. RH</p>
            </div>
            <div class="tl-meta">
              <div class="qty out">-5 unités</div>
              <div class="time">Aujourd'hui, 11:15</div>
            </div>
          </div>
          <div class="timeline-item">
            <div class="tl-icon adj"><i class="fas fa-sliders"></i></div>
            <div class="tl-info">
              <strong>Ajustement — Câble HDMI 2.0</strong>
              <p>Réf: PRD-001 · Opérateur: <strong>Admin</strong> · Motif: Inventaire physique</p>
            </div>
            <div class="tl-meta">
              <div class="qty in">Ajustement</div>
              <div class="time">Hier, 17:00</div>
            </div>
          </div>
          <div class="timeline-item">
            <div class="tl-icon out"><i class="fas fa-arrow-up"></i></div>
            <div class="tl-info">
              <strong>Sortie — Chaise de Bureau Ergonomique</strong>
              <p>Réf: PRD-015 · Opérateur: <strong>Jean Dupont</strong> · Client: Direction</p>
            </div>
            <div class="tl-meta">
              <div class="qty out">-2 unités</div>
              <div class="time">Hier, 09:45</div>
            </div>
          </div>
          <div class="timeline-item">
            <div class="tl-icon in"><i class="fas fa-arrow-down"></i></div>
            <div class="tl-info">
              <strong>Entrée — Souris Optique Sans Fil</strong>
              <p>Réf: PRD-012 · Opérateur: <strong>Admin</strong> · Fournisseur: MouseCo</p>
            </div>
            <div class="tl-meta">
              <div class="qty in">+20 unités</div>
              <div class="time">Il y a 3 jours</div>
            </div>
          </div>
        </div>
        <!-- PHP: endforeach; -->

        <div class="pagination">
          <span class="pagination-info">Affichage 1–5 sur 342 mouvements</span>
          <div class="pagination-btns">
            <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================
       SECTION 4 : GESTION UTILISATEURS
  ============================================================ -->
  <section class="page-section" id="sec-users">
    <div class="section-header">
      <div>
        <h3>Gestion des Utilisateurs</h3>
        <p>Administrez les comptes et leurs permissions</p>
      </div>
      <button class="btn btn-primary" onclick="openModal('modalAddUser')">
        <i class="fas fa-user-plus"></i> Nouvel Utilisateur
      </button>
    </div>

    <div class="card">
      <div class="table-controls">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" placeholder="Rechercher un utilisateur..."/>
        </div>
        <select class="filter-select">
          <option value="">Tous les rôles</option>
          <option value="admin">Administrateur</option>
          <option value="operator">Opérateur</option>
        </select>
      </div>

      <div class="card-body">
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Dernière Connexion</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- =============================================
                   PHP INJECTION POINT — Liste utilisateurs
                   foreach($utilisateurs as $user):
              ================================================ -->
              <tr>
                <td class="td-id">#1</td>
                <td>
                  <div class="user-cell">
                    <div class="user-avatar-sm" style="background:#0056b3;">AD</div>
                    <div>
                      <!-- PHP: echo $user['nom'] . ' ' . $user['prenom']; -->
                      <strong>Admin Principal</strong>
                      <div style="font-size:0.72rem;color:var(--text-muted)">admin</div>
                    </div>
                  </div>
                </td>
                <td style="color:var(--text-muted);font-size:0.85rem;">admin@stockflow.fr</td>
                <td><span class="badge badge-info">Administrateur</span></td>
                <td style="font-size:0.82rem;color:var(--text-muted);">Aujourd'hui, 08:30</td>
                <td><span class="badge badge-success"><span class="badge-dot"></span>Actif</span></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-action edit" title="Modifier"><i class="fas fa-pencil"></i></button>
                    <button class="btn-action delete" title="Désactiver" onclick="confirmDelete(1, 'Admin Principal')"><i class="fas fa-ban"></i></button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="td-id">#2</td>
                <td>
                  <div class="user-cell">
                    <div class="user-avatar-sm" style="background:#28a745;">JD</div>
                    <div>
                      <strong>Jean Dupont</strong>
                      <div style="font-size:0.72rem;color:var(--text-muted)">opérateur</div>
                    </div>
                  </div>
                </td>
                <td style="color:var(--text-muted);font-size:0.85rem;">j.dupont@stockflow.fr</td>
                <td><span class="badge badge-warning">Opérateur</span></td>
                <td style="font-size:0.82rem;color:var(--text-muted);">Aujourd'hui, 09:12</td>
                <td><span class="badge badge-success"><span class="badge-dot"></span>Actif</span></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-action edit" title="Modifier"><i class="fas fa-pencil"></i></button>
                    <button class="btn-action delete" title="Désactiver" onclick="confirmDelete(2, 'Jean Dupont')"><i class="fas fa-ban"></i></button>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="td-id">#3</td>
                <td>
                  <div class="user-cell">
                    <div class="user-avatar-sm" style="background:#dc3545;">MM</div>
                    <div>
                      <strong>Marie Martin</strong>
                      <div style="font-size:0.72rem;color:var(--text-muted)">opérateur</div>
                    </div>
                  </div>
                </td>
                <td style="color:var(--text-muted);font-size:0.85rem;">m.martin@stockflow.fr</td>
                <td><span class="badge badge-warning">Opérateur</span></td>
                <td style="font-size:0.82rem;color:var(--text-muted);">Hier, 17:45</td>
                <td><span class="badge badge-danger"><span class="badge-dot"></span>Inactif</span></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-action edit" title="Modifier"><i class="fas fa-pencil"></i></button>
                    <button class="btn-action delete" title="Activer"><i class="fas fa-check"></i></button>
                  </div>
                </td>
              </tr>
              <!-- PHP: endforeach; -->
            </tbody>
          </table>
        </div>
        <div class="pagination">
          <span class="pagination-info">Affichage 1–3 sur 3 utilisateurs</span>
          <div class="pagination-btns">
            <button class="page-btn active">1</button>
          </div>
        </div>
      </div>
    </div>
  </section>

</main><!-- end .main-content -->

<!-- ================================================================
     MODAL — AJOUTER / MODIFIER PRODUIT
================================================================ -->
<div class="modal-overlay" id="modalAddProduct">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-box"></i> <span id="modalProductTitle">Ajouter un Produit</span></h3>
      <button class="modal-close" onclick="closeModal('modalAddProduct')"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <!--
        PHP: Ce formulaire sera soumis via POST
        action="controllers/ProductController.php"
        method="POST"
      -->
      <form id="productForm" onsubmit="handleProductForm(event)">
        <!-- PHP: Champ caché pour l'ID en mode édition -->
        <input type="hidden" id="productId" name="id" value="">

        <div class="form-grid">
          <div class="form-group">
            <label>Référence <span>*</span></label>
            <input type="text" id="pRef" name="reference" class="form-control" placeholder="Ex: PRD-001" required/>
            <span class="form-hint">Format recommandé : PRD-XXX</span>
          </div>
          <div class="form-group">
            <label>Catégorie <span>*</span></label>
            <select id="pCategory" name="categorie" class="form-control" required>
              <option value="">Sélectionner...</option>
              <!-- PHP: foreach($categories as $cat): -->
              <option value="electronique">Électronique</option>
              <option value="informatique">Informatique</option>
              <option value="bureautique">Bureautique</option>
              <option value="mobilier">Mobilier</option>
              <!-- PHP: endforeach; -->
            </select>
          </div>
          <div class="form-group full">
            <label>Nom du Produit <span>*</span></label>
            <input type="text" id="pName" name="nom" class="form-control" placeholder="Nom descriptif du produit" required/>
          </div>
          <div class="form-group full">
            <label>Description</label>
            <textarea id="pDesc" name="description" class="form-control" rows="2" style="resize:vertical;" placeholder="Description optionnelle..."></textarea>
          </div>
          <div class="form-group">
            <label>Stock Initial <span>*</span></label>
            <input type="number" id="pStock" name="stock" class="form-control" placeholder="0" min="0" required/>
          </div>
          <div class="form-group">
            <label>Seuil d'Alerte <span>*</span></label>
            <input type="number" id="pSeuil" name="seuil" class="form-control" placeholder="0" min="0" required/>
            <span class="form-hint">Alerte déclenchée si Stock ≤ Seuil</span>
          </div>
          <div class="form-group">
            <label>Prix Unitaire (€)</label>
            <input type="number" id="pPrice" name="prix" class="form-control" placeholder="0.00" min="0" step="0.01"/>
          </div>
          <div class="form-group">
            <label>Fournisseur</label>
            <input type="text" id="pSupplier" name="fournisseur" class="form-control" placeholder="Nom du fournisseur"/>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modalAddProduct')">
        <i class="fas fa-xmark"></i> Annuler
      </button>
      <button class="btn btn-primary" onclick="document.getElementById('productForm').dispatchEvent(new Event('submit'))">
        <i class="fas fa-floppy-disk"></i> Enregistrer
      </button>
    </div>
  </div>
</div>

<!-- ================================================================
     MODAL — CONFIRMATION DE SUPPRESSION
================================================================ -->
<div class="modal-overlay" id="modalDelete">
  <div class="modal" style="max-width:440px;">
    <div class="modal-header">
      <h3><i class="fas fa-triangle-exclamation" style="color:var(--danger)"></i> Confirmer la Suppression</h3>
      <button class="modal-close" onclick="closeModal('modalDelete')"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="modal-delete-icon"><i class="fas fa-trash-can"></i></div>
      <div class="modal-delete-text">
        <h4>Êtes-vous sûr ?</h4>
        <p>Vous allez supprimer définitivement :<br>
          <strong id="deleteTargetName">—</strong>
        </p>
        <p style="margin-top:12px;font-size:0.8rem;color:var(--danger);">
          <i class="fas fa-exclamation-circle"></i>
          Cette action est irréversible. Tous les mouvements associés seront conservés.
        </p>
      </div>
      <!-- PHP: Formulaire POST vers le contrôleur de suppression -->
      <input type="hidden" id="deleteTargetId" name="id" value="">
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modalDelete')">
        <i class="fas fa-xmark"></i> Annuler
      </button>
      <button class="btn btn-danger" onclick="handleDelete()">
        <i class="fas fa-trash"></i> Oui, Supprimer
      </button>
    </div>
  </div>
</div>

<!-- ================================================================
     MODAL — AJOUTER UTILISATEUR
================================================================ -->
<div class="modal-overlay" id="modalAddUser">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-user-plus"></i> Nouvel Utilisateur</h3>
      <button class="modal-close" onclick="closeModal('modalAddUser')"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <form id="userForm" onsubmit="handleUserForm(event)">
        <div class="form-grid">
          <div class="form-group">
            <label>Prénom <span>*</span></label>
            <input type="text" name="prenom" class="form-control" placeholder="Prénom" required/>
          </div>
          <div class="form-group">
            <label>Nom <span>*</span></label>
            <input type="text" name="nom" class="form-control" placeholder="Nom de famille" required/>
          </div>
          <div class="form-group full">
            <label>Email <span>*</span></label>
            <input type="email" name="email" class="form-control" placeholder="email@stockflow.fr" required/>
          </div>
          <div class="form-group">
            <label>Mot de passe <span>*</span></label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required/>
          </div>
          <div class="form-group">
            <label>Rôle <span>*</span></label>
            <select name="role" class="form-control" required>
              <option value="">Sélectionner...</option>
              <option value="admin">Administrateur</option>
              <option value="operator">Opérateur</option>
            </select>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modalAddUser')"><i class="fas fa-xmark"></i> Annuler</button>
      <button class="btn btn-primary" onclick="document.getElementById('userForm').dispatchEvent(new Event('submit'))">
        <i class="fas fa-floppy-disk"></i> Créer le Compte
      </button>
    </div>
  </div>
</div>

<!-- ================================================================
     TOAST CONTAINER
================================================================ -->
<div class="toast-container" id="toastContainer"></div>

<!-- ================================================================
     JAVASCRIPT
================================================================ -->
<script>
  /* ============================================================
     NAVIGATION — Gestion des onglets (sections)
  ============================================================ */
  const pageMeta = {
    dashboard: { title: 'Dashboard',                   breadcrumb: 'Vue d\'ensemble' },
    products:  { title: 'Gestion des Produits',        breadcrumb: 'Produits' },
    history:   { title: 'Historique des Mouvements',   breadcrumb: 'Historique' },
    users:     { title: 'Gestion des Utilisateurs',    breadcrumb: 'Utilisateurs' },
  };

  function switchSection(sectionId, clickedBtn) {
    // Désactiver tous les items nav
    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
    // Cacher toutes les sections
    document.querySelectorAll('.page-section').forEach(sec => sec.classList.remove('active'));

    // Activer la section et le bouton
    if (clickedBtn) clickedBtn.classList.add('active');
    const section = document.getElementById('sec-' + sectionId);
    if (section) section.classList.add('active');

    // Mettre à jour le titre de la topbar
    const meta = pageMeta[sectionId] || { title: sectionId, breadcrumb: sectionId };
    document.getElementById('pageTitle').textContent = meta.title;
    document.getElementById('pageBreadcrumb').textContent = meta.breadcrumb;

    // Fermer la sidebar sur mobile
    closeSidebar();
  }

  /* ============================================================
     SIDEBAR MOBILE
  ============================================================ */
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const menuToggle = document.getElementById('menuToggle');

  menuToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
  });
  overlay.addEventListener('click', closeSidebar);

  function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
  }

  /* ============================================================
     DARK MODE
  ============================================================ */
  const themeToggle = document.getElementById('themeToggle');
  const html = document.documentElement;

  // Lire la préférence sauvegardée
  const savedTheme = localStorage.getItem('stockflow-admin-theme') || 'light';
  setTheme(savedTheme);

  function toggleTheme() {
    const current = html.getAttribute('data-theme');
    setTheme(current === 'dark' ? 'light' : 'dark');
  }

  function setTheme(theme) {
    html.setAttribute('data-theme', theme);
    themeToggle.classList.toggle('dark', theme === 'dark');
    localStorage.setItem('stockflow-admin-theme', theme);
  }

  /* ============================================================
     MODALS
  ============================================================ */
  function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('open');
    document.body.style.overflow = '';
  }

  // Fermer modal en cliquant sur l'overlay
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeModal(overlay.id);
    });
  });

  // Fermer avec Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
    }
  });

  /* ============================================================
     PRODUIT — Ouvrir modal en mode édition
  ============================================================ */
  function editProduct(id, ref, name, category, stock, seuil, price) {
    document.getElementById('modalProductTitle').textContent = 'Modifier le Produit';
    document.getElementById('productId').value   = id;
    document.getElementById('pRef').value        = ref;
    document.getElementById('pName').value       = name;
    document.getElementById('pCategory').value   = category;
    document.getElementById('pStock').value      = stock;
    document.getElementById('pSeuil').value      = seuil;
    document.getElementById('pPrice').value      = price;
    openModal('modalAddProduct');
  }

  function viewProduct(id) {
    // PHP: Rediriger vers la page de détail du produit
    // window.location.href = `product_detail.php?id=${id}`;
    showToast('Détail Produit', `Affichage du produit ID: ${id}`, 'success');
  }

  /* ============================================================
     CONFIRMATION SUPPRESSION
  ============================================================ */
  function confirmDelete(id, name) {
    document.getElementById('deleteTargetId').value = id;
    document.getElementById('deleteTargetName').textContent = name;
    openModal('modalDelete');
  }

  function handleDelete() {
    const id   = document.getElementById('deleteTargetId').value;
    const name = document.getElementById('deleteTargetName').textContent;
    // PHP: Envoyer une requête POST/DELETE au serveur
    // fetch(`controllers/ProductController.php?action=delete&id=${id}`, { method: 'POST' })
    closeModal('modalDelete');
    showToast('Suppression', `"${name}" a été supprimé.`, 'success');
    // PHP: Après suppression, recharger la page ou retirer la ligne du DOM
  }

  /* ============================================================
     FORMULAIRE PRODUIT — Soumission (simulation)
  ============================================================ */
  function handleProductForm(e) {
    e.preventDefault();
    const id   = document.getElementById('productId').value;
    const name = document.getElementById('pName').value;
    const action = id ? 'modifié' : 'ajouté';
    // PHP: Les données seront soumises via ce formulaire
    // e.target.action = 'controllers/ProductController.php';
    // e.target.submit();
    closeModal('modalAddProduct');
    showToast('Produit ' + action, `"${name}" a été ${action} avec succès.`, 'success');
    // Réinitialiser le formulaire
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('modalProductTitle').textContent = 'Ajouter un Produit';
  }

  /* ============================================================
     FORMULAIRE UTILISATEUR
  ============================================================ */
  function handleUserForm(e) {
    e.preventDefault();
    // PHP: e.target.action = 'controllers/UserController.php'; e.target.submit();
    closeModal('modalAddUser');
    showToast('Utilisateur Créé', 'Le compte a été créé avec succès.', 'success');
    document.getElementById('userForm').reset();
  }

  /* ============================================================
     FILTRE / RECHERCHE TABLE PRODUITS
  ============================================================ */
  function filterTable() {
    const search   = document.getElementById('searchInput').value.toLowerCase().trim();
    const category = document.getElementById('categoryFilter').value.toLowerCase();
    const stockF   = document.getElementById('stockFilter').value;
    const rows     = document.querySelectorAll('#productsTableBody tr');

    rows.forEach(row => {
      const text     = row.textContent.toLowerCase();
      const rowCat   = (row.dataset.category || '').toLowerCase();
      const rowStock = (row.dataset.stock || '').toLowerCase();

      const matchSearch   = !search   || text.includes(search);
      const matchCategory = !category || rowCat === category;
      const matchStock    = !stockF   || rowStock === stockF;

      row.style.display = (matchSearch && matchCategory && matchStock) ? '' : 'none';
    });
  }

  /* ============================================================
     DÉCONNEXION
  ============================================================ */
  function handleLogout(e) {
    e.preventDefault();
    if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
      // PHP: window.location.href = 'logout.php';
      showToast('Déconnexion', 'À bientôt !', 'success');
    }
  }

  /* ============================================================
     TOAST NOTIFICATIONS
  ============================================================ */
  function showToast(title, message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
      <i class="fas ${icons[type] || icons.success} toast-icon"></i>
      <div class="toast-text">
        <strong>${title}</strong>
        <small>${message}</small>
      </div>
    `;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(40px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  }
</script>
</body>
</html>
