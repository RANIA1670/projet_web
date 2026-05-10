<?php
/**
 * Vue Back-Office : Tableau de bord CityZen — Design officiel
 */

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

if ($currentUserId === 0 || !$isAdmin) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../models/Report.php';
$controller = new ForumController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $postId = (int)($_POST['post_id'] ?? 0);
        if ($postId > 0) {
            if ($_POST['action'] === 'valider_post') {
                $controller->validerPost($postId);
            } elseif ($_POST['action'] === 'supprimer_post') {
                $controller->supprimerPost($postId);
            }
        }
        header('Location: index.php?page=dashboard');
        exit;
    }
}

$openReports = Report::getOpenGrouped();

// Stats
$allPosts     = $controller->listAllPosts();
$totalPosts   = count($allPosts);
$totalReplies = 0;
$totalLikes   = 0;
foreach ($allPosts as $p) {
    $totalReplies += $controller->countRepliesByPost($p->getId());
    $totalLikes   += $controller->countPostLikes($p->getId());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — CityZen Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── Palette CityZen ─────────────────────────────────── */
        :root {
            --sidebar-bg:   #2F3C4F;
            --header-bg:    #2F3C4F;
            --page-bg:      #F4F6F8;
            --card-bg:      #FFFFFF;

            --green:        #2ECC71;
            --navy:         #34495E;
            --orange:       #F39C12;
            --gray-btn:     #95A5A6;

            --title:        #2C3E50;
            --text-sec:     #7F8C8D;
            --link-inactive:#9BA4B5;
            --link-active:  #FFFFFF;
            --stat-sidebar: #2F3C4F;

            --radius:       10px;
            --shadow:       0 2px 12px rgba(0,0,0,.08);
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            color: var(--title);
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ─────────────────────────────────────────── */
        .sidebar {
            width: 220px;
            min-height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
        }
        .sidebar-brand {
            padding: 24px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand .logo {
            font-size: 1.25rem;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -.5px;
            line-height: 1.25;
        }
        .sidebar-brand .logo .admin-mark {
            display: block;
            margin-top: 4px;
            font-size: .65rem;
            font-weight: 700;
            color: var(--orange);
            text-transform: uppercase;
            letter-spacing: .12em;
        }
        .sidebar-section {
            padding: 18px 16px 4px;
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--link-inactive);
        }
        .sidebar-nav { list-style: none; padding: 4px 10px; }
        .sidebar-nav li { margin-bottom: 2px; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            color: var(--link-inactive);
            transition: all .2s;
        }
        .sidebar-nav a:hover {
            background: rgba(255,255,255,.08);
            color: var(--link-active);
        }
        .sidebar-nav a.active {
            background: rgba(255,255,255,.12);
            color: var(--link-active);
            font-weight: 600;
        }
        .sidebar-nav a .nav-icon { font-size: 1rem; }
        .sidebar-indicator {
            width: 3px;
            height: 100%;
            background: var(--green);
            border-radius: 2px;
            position: absolute;
            left: 0;
        }
        .sidebar-nav li.active-item {
            position: relative;
        }
        .sidebar-nav li.active-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: var(--green);
            border-radius: 0 3px 3px 0;
        }

        /* ── Header ──────────────────────────────────────────── */
        .main-wrapper {
            margin-left: 220px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .header {
            background: var(--header-bg);
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
        }
        .header-link {
            font-size: .825rem;
            color: #FFFFFF;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,.18);
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .header-link:hover {
            background: rgba(255,255,255,.1);
            color: #FFFFFF;
        }

        /* ── Page Content ────────────────────────────────────── */
        .page-content {
            padding: 32px 36px;
            flex: 1;
        }
        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--title);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-title-line {
            height: 3px;
            width: 100%;
            background: var(--green);
            border-radius: 2px;
            margin-bottom: 14px;
        }
        .page-subtitle {
            font-size: .875rem;
            color: var(--text-sec);
            margin-bottom: 28px;
        }

        /* ── Stat Circles ────────────────────────────────────── */
        .stats-circles {
            display: flex;
            gap: 32px;
            margin-bottom: 36px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .stat-circle-card {
            background: var(--card-bg);
            border-radius: 50%;
            width: 150px;
            height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            border: 3px solid #eee;
            transition: transform .25s, box-shadow .25s;
            text-align: center;
            padding: 16px;
        }
        .stat-circle-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
        }
        .stat-circle-card.orange-ring { border-color: #FDEBD0; }
        .stat-circle-card.blue-ring   { border-color: #D6EAF8; }

        .stat-circle-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }
        .stat-circle-card:nth-child(1) .stat-circle-value { color: var(--green); }
        .stat-circle-card.orange-ring .stat-circle-value   { color: var(--orange); }
        .stat-circle-card.blue-ring .stat-circle-value     { color: var(--stat-sidebar); }

        .stat-circle-pct {
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .stat-circle-card:nth-child(1) .stat-circle-pct { color: var(--green); }
        .stat-circle-card.orange-ring .stat-circle-pct   { color: var(--orange); }
        .stat-circle-card.blue-ring .stat-circle-pct     { color: var(--stat-sidebar); }

        .stat-circle-label {
            font-size: .72rem;
            color: var(--text-sec);
            font-weight: 500;
            text-align: center;
            line-height: 1.3;
        }

        /* ── Section Title ───────────────────────────────────── */
        .section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--title);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title .section-icon {
            color: var(--orange);
        }

        /* ── Quick Actions ───────────────────────────────────── */
        .quick-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 32px;
        }
        .btn-action {
            padding: 11px 22px;
            border-radius: 8px;
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: filter .2s, transform .15s;
        }
        .btn-action:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }
        .btn-green  { background: var(--green);    color: #fff; }
        .btn-navy   { background: var(--navy);     color: #fff; }
        .btn-orange { background: var(--orange);   color: #fff; }
        .btn-gray   { background: var(--gray-btn); color: #fff; }

        /* ── Mini Stat Cards ─────────────────────────────────── */
        .mini-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 36px;
        }
        .mini-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 22px 24px;
            box-shadow: var(--shadow);
            border-bottom: 4px solid var(--green);
            transition: transform .2s, box-shadow .2s;
        }
        .mini-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,.1);
        }
        .mini-card-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--green);
            line-height: 1;
            margin-bottom: 6px;
        }
        .mini-card-label {
            font-size: .8rem;
            color: var(--text-sec);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Stats Section ───────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .stat-chart-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 22px 24px;
            box-shadow: var(--shadow);
        }
        .stat-chart-title {
            font-size: .875rem;
            font-weight: 600;
            color: var(--title);
            margin-bottom: 16px;
        }
        .chart-bar-wrap { display: flex; flex-direction: column; gap: 10px; }
        .chart-bar-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .78rem;
        }
        .chart-bar-label { color: var(--text-sec); min-width: 80px; }
        .chart-bar-outer {
            flex: 1;
            background: #F4F6F8;
            border-radius: 20px;
            height: 8px;
            overflow: hidden;
        }
        .chart-bar-inner {
            height: 100%;
            border-radius: 20px;
            background: var(--green);
            transition: width .8s ease;
        }
        .chart-bar-inner.orange { background: var(--orange); }
        .chart-bar-inner.navy   { background: var(--navy); }
        .chart-bar-count { color: var(--title); font-weight: 700; min-width: 24px; text-align: right; }

        /* ── Divider ─────────────────────────────────────────── */
        .divider {
            border: none;
            border-top: 1px solid #E8ECF0;
            margin: 28px 0;
        }

        /* ── Responsive ──────────────────────────────────────── */
        @media (max-width: 900px) {
            .sidebar { width: 60px; }
            .sidebar-brand .admin-mark,
            .sidebar-section, .sidebar-nav a span.nav-label { display: none; }
            .main-wrapper { margin-left: 60px; }
            .mini-stats, .stats-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 600px) {
            .page-content { padding: 20px 16px; }
            .stats-circles { justify-content: center; }
            .mini-stats, .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">CityZen<span class="admin-mark">ADMIN</span></div>
    </div>

    <div class="sidebar-section">Principal</div>
    <ul class="sidebar-nav">
        <li class="active-item">
            <a href="dashboard_cityzen.php" class="active">
                <span class="nav-icon">🏠</span>
                <span class="nav-label">Dashboard</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-section">Gestion</div>
    <ul class="sidebar-nav">
        <li>
            <a href="#">
                <span class="nav-icon">📅</span>
                <span class="nav-label">Événements</span>
            </a>
        </li>
        <li>
            <a href="#">
                <span class="nav-icon">🤝</span>
                <span class="nav-label">Sponsors</span>
            </a>
        </li>
        <li>
            <a href="#">
                <span class="nav-icon">👥</span>
                <span class="nav-label">Participations</span>
            </a>
        </li>
    </ul>
</aside>

<!-- ═══ MAIN WRAPPER ═══ -->
<div class="main-wrapper">

    <!-- Header -->
    <header class="header">
        <a href="../front_office/list_posts.php" class="header-link">← Front Office</a>
    </header>

    <!-- Page Content -->
    <main class="page-content">

        <h1 class="page-title">🏠 Tableau de bord</h1>
        <div class="page-title-line" role="presentation"></div>
        <p class="page-subtitle">Bienvenue dans l'interface d'administration CityZen.</p>

        <!-- Stat Circles -->
        <div class="stats-circles">
            <div class="stat-circle-card">
                <div class="stat-circle-value"><?= $totalPosts ?></div>
                <div class="stat-circle-pct"><?= $totalPosts > 0 ? '100%' : '0%' ?></div>
                <div class="stat-circle-label">Événements dans 7 jours</div>
            </div>
            <div class="stat-circle-card orange-ring">
                <div class="stat-circle-value"><?= $totalReplies ?></div>
                <div class="stat-circle-pct">80%</div>
                <div class="stat-circle-label">Sponsors actifs</div>
            </div>
            <div class="stat-circle-card blue-ring">
                <div class="stat-circle-value"><?= $totalLikes ?></div>
                <div class="stat-circle-pct">100%</div>
                <div class="stat-circle-label">Événements avec participants</div>
            </div>
        </div>

        <hr class="divider">

        <!-- ── Signalements ── -->
        <h2 class="section-title" id="signalements"><span class="section-icon">🚩</span> Posts Signalés</h2>
        <?php if (!empty($openReports)): ?>
        <div style="background:var(--card-bg);border:1px solid #E8ECF0;border-radius:var(--radius);overflow:hidden;margin-bottom:28px;box-shadow:var(--shadow);">
            <div style="padding:14px 20px;font-weight:700;font-size:.95rem;border-bottom:1px solid #E8ECF0;display:flex;align-items:center;gap:10px;">
                🚩 Signalements en attente
                <span style="background:rgba(231,76,60,.15);color:#c0392b;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px;"><?= count($openReports) ?> post(s) signalé(s)</span>
            </div>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: var(--page-bg); border-bottom: 2px solid #E8ECF0; font-size: 0.8rem; color: var(--text-sec); text-transform: uppercase;">
                    <tr>
                        <th style="padding: 12px 20px;">Post</th>
                        <th style="padding: 12px 20px;">Motif (Dernier)</th>
                        <th style="padding: 12px 20px;">Signalé par</th>
                        <th style="padding: 12px 20px;">Statut</th>
                        <th style="padding: 12px 20px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($openReports as $report): ?>
                    <tr style="border-bottom: 1px solid #E8ECF0;">
                        <td style="padding: 12px 20px; max-width: 250px;">
                            <div style="font-weight:600; color:var(--navy); font-size:0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= htmlspecialchars((string)($report['title'] ?? 'Post supprimé')) ?>
                            </div>
                            <div style="font-size:0.75rem; color:var(--text-sec); margin-top:2px;">ID: #<?= (int)$report['post_id'] ?></div>
                        </td>
                        <td style="padding: 12px 20px; font-size:.85rem;color:var(--text-sec);">
                            <?= htmlspecialchars((string)$report['last_reason']) ?><br>
                            <span style="font-size:0.75rem; color:#aaa;"><?= date('d/m/Y H:i', strtotime((string)$report['last_report_date'])) ?></span>
                        </td>
                        <td style="padding: 12px 20px;">
                            <span style="background: #FFF3CD; color: #856404; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                <?= (int)$report['report_count'] ?> signalement(s)
                            </span>
                        </td>
                        <td style="padding: 12px 20px;">
                            <?php if ($report['post_status'] === 'Masqué'): ?>
                                <span style="background: rgba(231,76,60,0.1); color: #e74c3c; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Masqué</span>
                            <?php else: ?>
                                <span style="background: rgba(46,204,113,0.1); color: #2ecc71; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">Actif</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px 20px; text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                            <form method="POST" action="index.php?page=dashboard" style="display:inline;">
                                <input type="hidden" name="action" value="valider_post">
                                <input type="hidden" name="post_id" value="<?= (int)$report['post_id'] ?>">
                                <button type="submit" title="Rejeter les signalements et garder le post" style="cursor:pointer; background:#2ecc71; color:white; border:none; border-radius:4px; padding:6px 10px; font-size:0.8rem; font-weight:600; transition:opacity 0.2s;">✅ Conserver</button>
                            </form>
                            <form method="POST" action="index.php?page=dashboard" style="display:inline;" onsubmit="return confirm('Supprimer ce post définitivement ?');">
                                <input type="hidden" name="action" value="supprimer_post">
                                <input type="hidden" name="post_id" value="<?= (int)$report['post_id'] ?>">
                                <button type="submit" title="Supprimer ce post et ses signalements" style="cursor:pointer; background:#e74c3c; color:white; border:none; border-radius:4px; padding:6px 10px; font-size:0.8rem; font-weight:600; transition:opacity 0.2s;">🗑️ Supprimer</button>
                            </form>
                            <a href="<?= htmlspecialchars(forum_list_url('page=post&id=' . (int)$report['post_id'])) ?>" target="_blank" title="Voir le post" style="text-decoration:none; background:#3498db; color:white; border:none; border-radius:4px; padding:6px 10px; font-size:0.8rem; font-weight:600; transition:opacity 0.2s;">👁️ Lien</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="background:var(--card-bg);border:1px solid #E8ECF0;border-radius:var(--radius);padding:16px 20px;margin-bottom:28px;font-size:.875rem;color:var(--text-sec);box-shadow:var(--shadow);">
            ✅ Aucun signalement en attente.
        </div>
        <?php endif; ?>

        <hr class="divider">

        <!-- Quick Actions -->
        <div class="section-title"><span class="section-icon" aria-hidden="true">⚡</span> Actions rapides</div>
        <div class="quick-actions">
            <a href="#" class="btn-action btn-green">+ Ajouter un événement</a>
            <a href="#" class="btn-action btn-navy">+ Ajouter un sponsor</a>
            <a href="#" class="btn-action btn-orange">+ Ajouter une participation</a>
            <a href="#" class="btn-action btn-gray">📩 Envoyer rappels</a>
        </div>

        <!-- Mini Stats -->
        <div class="mini-stats">
            <div class="mini-card">
                <div class="mini-card-value"><?= $totalPosts ?></div>
                <div class="mini-card-label">📅 Événements dans 7 jours</div>
            </div>
            <div class="mini-card">
                <div class="mini-card-value"><?= $totalReplies ?></div>
                <div class="mini-card-label">🤝 Sponsor le plus actif</div>
            </div>
            <div class="mini-card">
                <div class="mini-card-value"><?= $totalLikes ?></div>
                <div class="mini-card-label">🔥 Participants max sur un événement</div>
            </div>
        </div>

        <hr class="divider">

        <!-- Statistiques -->
        <div class="section-title"><span class="section-icon" aria-hidden="true">📊</span> Statistiques</div>
        <div class="stats-grid">
            <div class="stat-chart-card">
                <div class="stat-chart-title">Événements avec / sans sponsors</div>
                <div class="chart-bar-wrap">
                    <div class="chart-bar-row">
                        <span class="chart-bar-label">Avec sponsor</span>
                        <div class="chart-bar-outer"><div class="chart-bar-inner" style="width:75%"></div></div>
                        <span class="chart-bar-count">3</span>
                    </div>
                    <div class="chart-bar-row">
                        <span class="chart-bar-label">Sans sponsor</span>
                        <div class="chart-bar-outer"><div class="chart-bar-inner orange" style="width:25%"></div></div>
                        <span class="chart-bar-count">1</span>
                    </div>
                </div>
            </div>
            <div class="stat-chart-card">
                <div class="stat-chart-title">Participations par événement</div>
                <div class="chart-bar-wrap">
                    <div class="chart-bar-row">
                        <span class="chart-bar-label">Discussions</span>
                        <div class="chart-bar-outer"><div class="chart-bar-inner navy" style="width:60%"></div></div>
                        <span class="chart-bar-count"><?= $totalPosts ?></span>
                    </div>
                    <div class="chart-bar-row">
                        <span class="chart-bar-label">Réponses</span>
                        <div class="chart-bar-outer"><div class="chart-bar-inner" style="width:<?= min(100, $totalReplies * 10) ?>%"></div></div>
                        <span class="chart-bar-count"><?= $totalReplies ?></span>
                    </div>
                    <div class="chart-bar-row">
                        <span class="chart-bar-label">Likes</span>
                        <div class="chart-bar-outer"><div class="chart-bar-inner orange" style="width:<?= min(100, $totalLikes * 10) ?>%"></div></div>
                        <span class="chart-bar-count"><?= $totalLikes ?></span>
                    </div>
                </div>
            </div>
            <div class="stat-chart-card">
                <div class="stat-chart-title">Événements dans 7 jours</div>
                <div class="chart-bar-wrap">
                    <div class="chart-bar-row">
                        <span class="chart-bar-label">Cette semaine</span>
                        <div class="chart-bar-outer"><div class="chart-bar-inner" style="width:40%"></div></div>
                        <span class="chart-bar-count">2</span>
                    </div>
                    <div class="chart-bar-row">
                        <span class="chart-bar-label">Semaine proch.</span>
                        <div class="chart-bar-outer"><div class="chart-bar-inner orange" style="width:60%"></div></div>
                        <span class="chart-bar-count">3</span>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

</body>
</html>
