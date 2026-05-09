<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityZen Admin | <?= htmlspecialchars($pageTitle ?? 'Administration') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sidebar-w: 260px;
            --bg:        #F4F6F9;
            --surface:   #FFFFFF;
            --surface2:  #F1F3F5;
            --border:    #E8ECF0;
            --accent:    #27AE60;
            --accent2:   #E67E22;
            --danger:    #E74C3C;
            --success:   #2ECC71;
            --warning:   #F1C40F;
            --text:      #2C3E50;
            --text-muted:#95A5A6;
            --radius:    12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            font-size: 14px;
        }

        /* ── SIDEBAR ── */
        .admin-sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            z-index: 100;
            overflow-y: auto;
            transition: transform .3s;
        }

        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-brand .brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: var(--white);
            flex-shrink: 0;
        }
        .sidebar-brand .brand-name {
            font-size: 18px; font-weight: 700;
            color: var(--text);
        }
        .sidebar-brand .brand-sub {
            font-size: 10px; color: var(--text-muted); display: block; margin-top: 1px;
            letter-spacing: 1px; text-transform: uppercase;
        }

        .sidebar-nav { padding: 16px 0; flex: 1; }

        .nav-section-label {
            font-size: 10px; font-weight: 600; letter-spacing: 1.5px;
            text-transform: uppercase; color: var(--text-muted);
            padding: 12px 20px 6px;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 20px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 0;
            transition: all .2s;
            border-left: 3px solid transparent;
            font-weight: 500;
        }
        .sidebar-link:hover {
            color: var(--text);
            background: rgba(39,174,96,.08);
            border-left-color: var(--accent);
        }
        .sidebar-link.active {
            color: var(--accent);
            background: rgba(39,174,96,.12);
            border-left-color: var(--accent);
        }
        .sidebar-link i { width: 18px; text-align: center; font-size: 15px; }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border);
        }
        .admin-user-info {
            display: flex; align-items: center; gap: 10px; margin-bottom: 12px;
        }
        .admin-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: #fff; flex-shrink: 0;
        }
        .admin-user-name { font-weight: 600; font-size: 13px; }
        .admin-user-role { font-size: 11px; color: var(--text-muted); }

        .btn-logout {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; border-radius: var(--radius);
            background: rgba(231,76,60,.08); color: var(--danger);
            text-decoration: none; font-size: 13px; font-weight: 600;
            transition: background .2s; width: 100%;
        }
        .btn-logout:hover { background: rgba(231,76,60,.16); }

        .btn-front {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; border-radius: var(--radius);
            background: rgba(39,174,96,.12); color: var(--accent);
            text-decoration: none; font-size: 13px; font-weight: 600;
            transition: background .2s; width: 100%; margin-bottom: 8px;
        }
        .btn-front:hover { background: rgba(39,174,96,.2); }

        /* ── MAIN ── */
        .admin-main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── TOPBAR ── */
        .admin-topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title {
            font-size: 16px; font-weight: 600;
        }
        .topbar-breadcrumb {
            font-size: 12px; color: var(--text-muted);
        }
        .topbar-breadcrumb span { color: var(--accent); }
        .topbar-right { display: flex; align-items: center; gap: 14px; }
        .topbar-badge {
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 20px; padding: 4px 12px; font-size: 12px; color: var(--text-muted);
        }

        /* ── FLASH ── */
        .flash-admin {
            margin: 20px 28px 0;
            padding: 12px 18px;
            border-radius: var(--radius);
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 500;
        }
        .flash-admin.flash-success { background: rgba(46,213,115,.12); border: 1px solid rgba(46,213,115,.3); color: var(--success); }
        .flash-admin.flash-error   { background: rgba(255,71,87,.12);  border: 1px solid rgba(255,71,87,.3);  color: var(--danger); }
        .flash-admin.flash-info    { background: rgba(108,99,255,.12); border: 1px solid rgba(108,99,255,.3); color: var(--accent); }

        /* ── CONTENT ── */
        .admin-content {
            padding: 28px;
            flex: 1;
        }

        /* ── CARDS / STATS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            transition: border-color .2s, transform .2s;
        }
        .stat-card:hover { border-color: var(--accent); transform: translateY(-2px); }
        .stat-card .stat-icon {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; margin-bottom: 14px;
        }
        .stat-card .stat-value { font-size: 28px; font-weight: 700; }
        .stat-card .stat-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

        /* ── TABLE ── */
        .admin-table-wrap {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .admin-table-head {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .admin-table-head h3 { font-size: 15px; font-weight: 600; }

        table.admin-table {
            width: 100%; border-collapse: collapse;
        }
        table.admin-table th {
            text-align: left; padding: 12px 16px;
            font-size: 11px; font-weight: 600; letter-spacing: .8px;
            text-transform: uppercase; color: var(--text-muted);
            background: var(--surface2); border-bottom: 1px solid var(--border);
        }
        table.admin-table td {
            padding: 13px 16px; border-bottom: 1px solid var(--border);
            font-size: 13px; vertical-align: middle;
        }
        table.admin-table tr:last-child td { border-bottom: none; }
        table.admin-table tbody tr:hover { background: var(--surface2); }

        /* ── BADGES ── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
        }
        .badge-nouveau     { background: rgba(108,99,255,.15); color: var(--accent); }
        .badge-en_cours    { background: rgba(255,165,2,.15);  color: var(--warning); }
        .badge-en_attente  { background: rgba(139,144,176,.15);color: var(--text-muted); }
        .badge-resolu, .badge-termine, .badge-terminee { background: rgba(46,213,115,.15); color: var(--success); }
        .badge-planifiee   { background: rgba(78,205,196,.15); color: var(--accent2); }
        .badge-annule, .badge-rejetee { background: rgba(255,71,87,.15); color: var(--danger); }
        .badge-haute, .badge-urgente  { background: rgba(255,71,87,.15); color: var(--danger); }
        .badge-moyenne     { background: rgba(255,165,2,.15);  color: var(--warning); }
        .badge-faible      { background: rgba(46,213,115,.15); color: var(--success); }
        .badge-admin       { background: rgba(108,99,255,.2);  color: var(--accent); }
        .badge-technicien  { background: rgba(78,205,196,.2);  color: var(--accent2); }
        .badge-citoyen     { background: rgba(139,144,176,.2); color: var(--text-muted); }

        /* ── BUTTONS ── */
        .btn-admin {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer;
            font-size: 13px; font-weight: 500; text-decoration: none;
            transition: all .2s;
        }
        .btn-admin-primary { background: var(--accent); color: #fff; }
        .btn-admin-primary:hover { background: #5a52d5; }
        .btn-admin-danger  { background: var(--danger); color: #fff; }
        .btn-admin-danger:hover { background: #e0384a; }
        .btn-admin-ghost   { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
        .btn-admin-ghost:hover { border-color: var(--accent); color: var(--accent); }
        .btn-admin-sm { padding: 5px 10px; font-size: 12px; }

        /* Generic UI helpers */
        .container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .row { display: flex; flex-wrap: wrap; margin: 0 -12px; }
        .col-md-2, .col-md-3, .col-md-4, .col-md-6, .col-md-12 { padding: 0 12px; }
        .col-md-2 { width: 16.6667%; }
        .col-md-3 { width: 25%; }
        .col-md-4 { width: 33.3333%; }
        .col-md-6 { width: 50%; }
        .col-md-12 { width: 100%; }
        .d-flex { display: flex; }
        .flex-wrap { flex-wrap: wrap; }
        .align-items-center { align-items: center; }
        .justify-content-between { justify-content: space-between; }
        .justify-content-end { justify-content: flex-end; }
        .gap-1 { gap: 8px; }
        .gap-2 { gap: 12px; }
        .gap-3 { gap: 18px; }
        .g-4 { gap: 24px; }
        .mb-0 { margin-bottom: 0 !important; }
        .mb-2 { margin-bottom: 12px !important; }
        .mb-3 { margin-bottom: 18px !important; }
        .mb-4 { margin-bottom: 24px !important; }
        .mt-2 { margin-top: 12px !important; }
        .mt-3 { margin-top: 18px !important; }
        .mt-4 { margin-top: 24px !important; }
        .p-4 { padding: 24px !important; }
        .py-5 { padding-top: 40px !important; padding-bottom: 40px !important; }
        .h-100 { height: 100%; }
        .rounded { border-radius: var(--radius); }
        .shadow-sm { box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .text-center { text-align: center; }
        .text-muted { color: var(--text-muted); }
        .text-primary { color: var(--accent); }
        .text-warning { color: var(--warning); }
        .text-danger { color: var(--danger); }
        .bg-white { background: #fff; }
        .bg-light { background: #f8f9fa; }
        .bg-success { background: #2ECC71; color: #fff; }
        .bg-warning { background: #F1C40F; color: #212529; }
        .bg-secondary { background: #f1f3f5; color: #495057; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: 0.8rem; font-weight: 600; }
        .badge-primary { background: rgba(39,174,96,.12); color: var(--accent); }
        .badge-success { background: rgba(46,213,115,.15); color: var(--success); }
        .badge-warning { background: rgba(241,196,15,.15); color: var(--warning); }
        .badge-danger { background: rgba(231,76,60,.15); color: var(--danger); }
        .badge-secondary { background: rgba(148,163,184,.18); color: #495057; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 18px; border-radius: 8px; cursor: pointer; border: 1px solid transparent; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: all .2s; }
        .btn-sm { padding: 8px 14px; font-size: 0.82rem; }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: #1c8c4f; }
        .btn-outline { background: transparent; border-color: var(--text-muted); color: var(--text); }
        .btn-outline:hover { background: var(--surface2); }
        .btn-outline-secondary { background: transparent; border-color: #ced4da; color: var(--text); }
        .btn-outline-success { background: transparent; border-color: var(--accent); color: var(--accent); }
        .btn-outline-danger { background: transparent; border-color: var(--danger); color: var(--danger); }
        .form-control, .form-select, textarea {
            width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface2); color: var(--text); font-size: 0.95rem;
        }
        .form-control:focus, .form-select:focus, textarea:focus { outline: none; border-color: var(--accent); }
        .form-label { display: block; margin-bottom: 8px; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; }
        .table-responsive { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 14px 16px; border-bottom: 1px solid var(--border); }
        .table th { background: var(--surface2); color: var(--text-muted); text-transform: uppercase; font-size: 0.8rem; letter-spacing: .08em; }
        .table tr:hover { background: rgba(39,174,96,.05); }
        .admin-form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            max-width: 700px;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block; margin-bottom: 6px;
            font-size: 12px; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: .6px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px 14px;
            background: var(--surface2); border: 1px solid var(--border);
            border-radius: 8px; color: var(--text); font-family: inherit; font-size: 14px;
            transition: border-color .2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: var(--accent);
        }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-group select option { background: var(--surface2); }

        /* ── SECTION TITLE ── */
        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px;
        }
        .section-header h2 { font-size: 20px; font-weight: 700; }

        /* ── MOBILE TOGGLE ── */
        .sidebar-toggle {
            display: none;
            background: none; border: none; color: var(--text); font-size: 22px; cursor: pointer;
        }

        @media (max-width: 900px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-main { margin-left: 0; }
            .sidebar-toggle { display: block; }
        }
    </style>
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-city"></i></div>
        <div>
            <span class="brand-name">CityZen</span>
            <span class="brand-sub">Administration</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>
        <a href="<?= APP_URL ?>/admin" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/') ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="nav-section-label">Gestion</div>
        <a href="<?= APP_URL ?>/admin/signalements" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/signalements') ? 'active' : '' ?>">
            <i class="fas fa-exclamation-triangle"></i> Signalements
        </a>
        <a href="<?= APP_URL ?>/admin/interventions" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/interventions') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/intervention/') ? 'active' : '' ?>">
            <i class="fas fa-tools"></i> Interventions
        </a>
        <a href="<?= APP_URL ?>/admin/techniciens" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/techniciens') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/technicien/') ? 'active' : '' ?>">
            <i class="fas fa-hard-hat"></i> Techniciens
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-user-info">
            <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user_prenom'] ?? 'A', 0, 1)) ?></div>
            <div>
                <div class="admin-user-name"><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></div>
                <div class="admin-user-role"><i class="fas fa-shield-alt"></i> Administrateur</div>
            </div>
        </div>
        <a href="<?= APP_URL ?>/" class="btn-front">
            <i class="fas fa-globe"></i> Voir le site
        </a>
        <a href="<?= APP_URL ?>/auth/deconnexion" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</aside>

<!-- ═══ MAIN ═══ -->
<div class="admin-main">

    <!-- TOPBAR -->
    <header class="admin-topbar">
        <div style="display:flex;align-items:center;gap:14px;">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div>
                <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Administration') ?></div>
                <div class="topbar-breadcrumb">CityZen / <span>Admin</span></div>
            </div>
        </div>
        <div class="topbar-right">
            <span class="topbar-badge"><i class="fas fa-clock"></i> <?= date('d/m/Y H:i') ?></span>
        </div>
    </header>

    <!-- FLASH -->
    <?php if (isset($flash) && $flash): ?>
    <div class="flash-admin flash-<?= $flash['type'] ?>">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'times-circle' : 'info-circle') ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <!-- CONTENT -->
    <main class="admin-content">
        <?= $content ?>
    </main>
</div>

<script>
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
</script>

<!-- ========== COPILOT ZENO ========== -->
<link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/copilot.css">
<script>
    window.COPILOT_APP_URL = '<?= APP_URL ?>';
    window.COPILOT_USER_ROLE = '<?= $_SESSION['user_role'] ?? 'guest' ?>';
    window.COPILOT_USER_NAME = '<?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?>';
</script>
<script src="<?= APP_URL ?>/public/assets/js/copilot-kb.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/copilot.js"></script>

</body>
</html>
