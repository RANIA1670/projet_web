<?php
/**
 * Vue Back-Office : Statistiques du forum
 */

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

if ($currentUserId === 0 || !$isAdmin) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../controllers/ForumController.php';
$controller = new ForumController();
$stats = $controller->getForumStats();

$monthly      = $stats['monthly']      ?? [];
$topViewed    = $stats['topViewed']    ?? [];
$topLiked     = $stats['topLiked']    ?? [];
$totalPosts   = $stats['totalPosts']   ?? 0;
$totalReplies = $stats['totalReplies'] ?? 0;
$totalLikes   = $stats['totalLikes']   ?? 0;
$totalViews   = $stats['totalViews']   ?? 0;

// Préparer les données pour le graphique mensuel
$maxCount  = 1;
foreach ($monthly as $m) {
    if ((int)$m['post_count'] > $maxCount) $maxCount = (int)$m['post_count'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques du Forum — CityZen Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1117;
            --surface: #1a1d27;
            --surface2: #22263a;
            --accent: #6c63ff;
            --accent2: #43e97b;
            --accent3: #f7971e;
            --accent4: #f64f59;
            --text: #e2e8f0;
            --muted: #8892a4;
            --border: rgba(255,255,255,0.07);
            --radius: 16px;
            --shadow: 0 8px 32px rgba(0,0,0,.45);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── Header ── */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 18px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .topbar-brand .icon { font-size: 1.6rem; }

        .topbar-nav { display: flex; gap: 12px; }

        .nav-btn {
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            transition: all .25s;
            border: 1px solid var(--border);
            color: var(--muted);
            background: transparent;
        }

        .nav-btn:hover { background: var(--surface2); color: var(--text); }
        .nav-btn.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        /* ── Layout ── */
        .page { max-width: 1280px; margin: 0 auto; padding: 36px 24px; }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .page-subtitle { color: var(--muted); margin-bottom: 36px; font-size: .95rem; }

        /* ── KPI Cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 26px 24px;
            position: relative;
            overflow: hidden;
            transition: transform .25s, box-shadow .25s;
        }

        .kpi-card:hover { transform: translateY(-4px); box-shadow: var(--shadow); }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 3px;
        }

        .kpi-card.purple::before  { background: linear-gradient(90deg, var(--accent), #a78bfa); }
        .kpi-card.green::before   { background: linear-gradient(90deg, var(--accent2), #06d6a0); }
        .kpi-card.orange::before  { background: linear-gradient(90deg, var(--accent3), #ffd700); }
        .kpi-card.red::before     { background: linear-gradient(90deg, var(--accent4), #fc5c7d); }

        .kpi-icon {
            font-size: 2rem;
            margin-bottom: 14px;
            display: block;
        }

        .kpi-value {
            font-size: 2.6rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 6px;
        }

        .kpi-card.purple  .kpi-value { color: var(--accent); }
        .kpi-card.green   .kpi-value { color: var(--accent2); }
        .kpi-card.orange  .kpi-value { color: var(--accent3); }
        .kpi-card.red     .kpi-value { color: var(--accent4); }

        .kpi-label { color: var(--muted); font-size: .875rem; font-weight: 500; }

        /* ── Section heading ── */
        .section-heading {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-heading::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Bar Chart (pure CSS) ── */
        .chart-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            margin-bottom: 40px;
        }

        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            height: 200px;
            padding-top: 10px;
        }

        .bar-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            height: 100%;
            justify-content: flex-end;
        }

        .bar {
            width: 100%;
            background: linear-gradient(180deg, var(--accent), #a78bfa);
            border-radius: 6px 6px 0 0;
            min-height: 4px;
            transition: opacity .25s;
            position: relative;
        }

        .bar:hover { opacity: .8; }

        .bar-tooltip {
            display: none;
            position: absolute;
            top: -32px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            font-size: .75rem;
            padding: 3px 8px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .bar:hover .bar-tooltip { display: block; }

        .bar-label {
            font-size: .7rem;
            color: var(--muted);
            text-align: center;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            height: 44px;
        }

        .no-data {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 200px;
            color: var(--muted);
            font-size: 1rem;
            gap: 10px;
        }

        /* ── Top tables ── */
        .tables-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 40px;
        }

        @media (max-width: 780px) { .tables-grid { grid-template-columns: 1fr; } }

        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .table-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            font-size: .95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .top-table { width: 100%; border-collapse: collapse; }

        .top-table tr { border-bottom: 1px solid var(--border); transition: background .2s; }
        .top-table tbody tr:hover { background: var(--surface2); }
        .top-table td { padding: 13px 22px; font-size: .875rem; }

        .rank {
            font-weight: 700;
            color: var(--muted);
            width: 36px;
        }

        .rank.gold   { color: #ffd700; }
        .rank.silver { color: #c0c0c0; }
        .rank.bronze { color: #cd7f32; }

        .post-link { color: var(--text); text-decoration: none; font-weight: 500; }
        .post-link:hover { color: var(--accent2); }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }

        .badge-views  { background: rgba(108,99,255,.15); color: var(--accent); }
        .badge-likes  { background: rgba(247,151,30,.15);  color: var(--accent3); }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .topbar { padding: 14px 16px; }
            .page   { padding: 20px 14px; }
            .kpi-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<nav class="topbar">
    <div class="topbar-brand">
        <span class="icon">📊</span> CityZen Admin
    </div>
    <div class="topbar-nav">
        <a href="dashboard.php" class="nav-btn">🛠️ Dashboard</a>
        <a href="statistics.php" class="nav-btn active">📈 Statistiques</a>
        <a href="../front_office/list_posts.php" class="nav-btn">👁️ Forum</a>
    </div>
</nav>

<div class="page">
    <h1 class="page-title">📈 Statistiques du Forum</h1>
    <p class="page-subtitle">Vue d'ensemble de l'activité — mise à jour en temps réel</p>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card purple">
            <span class="kpi-icon">💬</span>
            <div class="kpi-value"><?= number_format($totalPosts) ?></div>
            <div class="kpi-label">Discussions</div>
        </div>
        <div class="kpi-card green">
            <span class="kpi-icon">↩️</span>
            <div class="kpi-value"><?= number_format($totalReplies) ?></div>
            <div class="kpi-label">Réponses</div>
        </div>
        <div class="kpi-card orange">
            <span class="kpi-icon">👍</span>
            <div class="kpi-value"><?= number_format($totalLikes) ?></div>
            <div class="kpi-label">Likes</div>
        </div>
        <div class="kpi-card red">
            <span class="kpi-icon">👁️</span>
            <div class="kpi-value"><?= number_format($totalViews) ?></div>
            <div class="kpi-label">Vues totales</div>
        </div>
    </div>

    <!-- Monthly Chart -->
    <h2 class="section-heading">📅 Activité mensuelle (12 derniers mois)</h2>
    <div class="chart-card">
        <?php if (empty($monthly)): ?>
            <div class="no-data">📭 Aucune donnée disponible pour cette période</div>
        <?php else: ?>
            <div class="bar-chart">
                <?php foreach ($monthly as $row):
                    $pct = (int)$row['post_count'] / $maxCount * 100;
                    $label = date('M y', strtotime($row['month'] . '-01'));
                ?>
                    <div class="bar-group">
                        <div class="bar" style="height: <?= max(2, round($pct * 1.9)) ?>px">
                            <span class="bar-tooltip"><?= (int)$row['post_count'] ?> post<?= $row['post_count'] > 1 ? 's' : '' ?></span>
                        </div>
                        <span class="bar-label"><?= htmlspecialchars($label) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Top Posts -->
    <h2 class="section-heading">🏆 Classements</h2>
    <div class="tables-grid">

        <!-- Top Vues -->
        <div class="table-card">
            <div class="table-card-header">👁️ Top 5 — Plus vus</div>
            <?php if (empty($topViewed)): ?>
                <div class="no-data" style="height:120px;font-size:.875rem;">Aucun post</div>
            <?php else: ?>
                <table class="top-table">
                    <tbody>
                        <?php foreach ($topViewed as $i => $row):
                            $rankClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : ''));
                        ?>
                            <tr>
                                <td class="rank <?= $rankClass ?>">#<?= $i + 1 ?></td>
                                <td>
                                    <a href="../front_office/view_post.php?id=<?= (int)$row['id'] ?>"
                                       class="post-link">
                                        <?= htmlspecialchars(mb_substr($row['title'], 0, 45)) ?>
                                        <?= mb_strlen($row['title']) > 45 ? '…' : '' ?>
                                    </a>
                                </td>
                                <td style="text-align:right">
                                    <span class="badge badge-views">👁️ <?= number_format($row['view_count']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Top Likes -->
        <div class="table-card">
            <div class="table-card-header">👍 Top 5 — Plus aimés</div>
            <?php if (empty($topLiked)): ?>
                <div class="no-data" style="height:120px;font-size:.875rem;">Aucun post</div>
            <?php else: ?>
                <table class="top-table">
                    <tbody>
                        <?php foreach ($topLiked as $i => $row):
                            $rankClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : ''));
                        ?>
                            <tr>
                                <td class="rank <?= $rankClass ?>">#<?= $i + 1 ?></td>
                                <td>
                                    <a href="../front_office/view_post.php?id=<?= (int)$row['id'] ?>"
                                       class="post-link">
                                        <?= htmlspecialchars(mb_substr($row['title'], 0, 45)) ?>
                                        <?= mb_strlen($row['title']) > 45 ? '…' : '' ?>
                                    </a>
                                </td>
                                <td style="text-align:right">
                                    <span class="badge badge-likes">👍 <?= number_format($row['like_count']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div><!-- /tables-grid -->

</div><!-- /page -->
</body>
</html>
