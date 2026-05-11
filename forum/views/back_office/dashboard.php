<?php
/**
 * Vue Back-Office : Tableau de bord de modération
 */

require_once __DIR__ . '/../../config/ForumRedirect.php';

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

if ($currentUserId === 0 || !$isAdmin) {
    header('Location: ' . forum_list_url('page=home'));
    exit;
}

require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Report.php';
require_once __DIR__ . '/../../models/EmailSubscription.php';
$controller = new ForumController();

// ── Actions POST ──────────────────────────────────────────────
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['item_id'])) {
    $itemId = (int)$_POST['item_id'];
    $action = $_POST['action'];

    if ($action === 'delete_post') {
        $message = $controller->deletePost($itemId)
            ? '<div class="toast toast-success">✅ Discussion supprimée avec succès.</div>'
            : '<div class="toast toast-error">❌ Erreur lors de la suppression.</div>';
    } elseif ($action === 'feature_post') {
        $message = Post::setFeatured($itemId, true)
            ? '<div class="toast toast-success">📌 Post mis en avant.</div>'
            : '<div class="toast toast-error">❌ Impossible de mettre en avant.</div>';
    } elseif ($action === 'unfeature_post') {
        $message = Post::setFeatured($itemId, false)
            ? '<div class="toast toast-success">✅ Post retiré des mises en avant.</div>'
            : '<div class="toast toast-error">❌ Impossible de retirer la mise en avant.</div>';
    } elseif ($action === 'resolve_report') {
        $message = Report::resolve($itemId)
            ? '<div class="toast toast-success">✅ Signalement traité.</div>'
            : '<div class="toast toast-error">❌ Impossible de traiter le signalement.</div>';
    } elseif ($action === 'hide_post') {
        $message = Report::hidePost($itemId)
            ? '<div class="toast toast-success">🙈 Post masqué de la vue publique.</div>'
            : '<div class="toast toast-error">❌ Impossible de masquer le post.</div>';
    } elseif ($action === 'resolve_post_reports') {
        $message = Report::resolveAllReportsForPost($itemId)
            ? '<div class="toast toast-success">✅ Tous les signalements rejetés.</div>'
            : '<div class="toast toast-error">❌ Impossible de rejeter les signalements.</div>';
    } elseif ($action === 'delete_reply') {
        require_once __DIR__ . '/../../models/Reply.php';
        $reply = Reply::findById($itemId);
        if ($reply) {
            $message = $controller->deleteReply($itemId)
                ? '<div class="toast toast-success">✅ Réponse supprimée avec succès.</div>'
                : '<div class="toast toast-error">❌ Erreur lors de la suppression.</div>';
        }
    }
}

// ── Recherche / Filtrage ──────────────────────────────────────
$keyword   = trim((string)($_GET['q']        ?? ''));
$dateFrom  = trim((string)($_GET['date_from'] ?? ''));
$dateTo    = trim((string)($_GET['date_to']   ?? ''));
$sortBy    = in_array($_GET['sort'] ?? '', ['created_at','view_count','title']) ? $_GET['sort'] : 'created_at';
$order     = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

if ($keyword !== '') {
    $posts = $controller->searchPosts($keyword, $sortBy, $order);
} elseif ($dateFrom !== '' || $dateTo !== '') {
    $posts = $controller->filterPosts(
        $dateFrom !== '' ? $dateFrom : null,
        $dateTo   !== '' ? $dateTo   : null,
        $sortBy,
        $order
    );
} else {
    $posts = $controller->filterPosts(null, null, $sortBy, $order);
}

// ── Stats rapides (même source que la page Statistiques) ───────
$stats        = $controller->getForumStats();
$totalPosts   = (int)($stats['totalPosts'] ?? 0);
$totalReplies = (int)($stats['totalReplies'] ?? 0);
$totalLikes   = (int)($stats['totalLikes'] ?? 0);
$openReports = Report::getOpenGrouped(20);
$openReportsEnriched = Report::getOpenEnriched(50);
$topReasons = Report::getTopReasons(5);
$totalOpenReports = Report::countOpen();
$activeEmailSubs = EmailSubscription::countActive();
$recentEmailSubs = EmailSubscription::listRecent(5);

// Grouper les signalements par post
$reportsByPost = [];
foreach ($openReportsEnriched as $report) {
    $postId = (int)$report['post_id'];
    if (!isset($reportsByPost[$postId])) {
        $reportsByPost[$postId] = [
            'post_id' => $postId,
            'title' => $report['title'],
            'content' => $report['content'],
            'author_id' => $report['author_id'],
            'post_status' => $report['post_status'],
            'view_count' => $report['view_count'],
            'report_count' => (int)$report['total_report_count'],
            'reports' => []
        ];
    }
    $reportsByPost[$postId]['reports'][] = [
        'id' => (int)$report['id'],
        'reason' => $report['reason'],
        'reporter_user_id' => (int)$report['reporter_user_id'],
        'created_at' => $report['created_at']
    ];
}
// Trier par nombre de signalements
uasort($reportsByPost, function($a, $b) {
    return $b['report_count'] <=> $a['report_count'];
});
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
        :root {
            /* ── Palette CityZen officielle ── */
            --bg:       #F4F6F8;          /* Fond principal */
            --surface:  #2F3C4F;          /* Sidebar / Header */
            --surface2: #3d5166;          /* Surface secondaire (hover) */
            --accent:   #34495E;          /* Bleu marine */
            --accent2:  #2ECC71;          /* Vert */
            --accent3:  #F39C12;          /* Orange */
            --accent4:  #E74C3C;          /* Rouge (erreurs) */
            --card:     #FFFFFF;          /* Fond des cartes */
            --text:     #2C3E50;          /* Titres principaux */
            --muted:    #7F8C8D;          /* Textes secondaires */
            --nav-inactive: #9BA4B5;      /* Liens inactifs sidebar */
            --nav-active:   #FFFFFF;      /* Liens actifs sidebar */
            --border:   rgba(0,0,0,0.07);
            --radius:   12px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* Topbar */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 200;
        }
        .topbar-brand {
            font-size: 1.2rem;
            font-weight: 700;
            color: #FFFFFF;
            font-weight: 800;
            letter-spacing: -.3px;
        }
        .topbar-nav { display: flex; gap: 10px; }
        .nav-btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            color: var(--nav-inactive);
            border: 1px solid rgba(255,255,255,.15);
            transition: all .2s;
        }
        .nav-btn:hover { background: rgba(255,255,255,.08); color: var(--nav-active); }
        .nav-btn.active { background: var(--accent2); color: #fff; border-color: var(--accent2); }

        /* Layout */
        .page { max-width: 1280px; margin: 0 auto; padding: 32px 24px; }

        /* Toast */
        .toast {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-weight: 500;
            font-size: .9rem;
            animation: slideIn .3s ease;
        }
        .toast-success { background: rgba(67,233,123,.12); border: 1px solid rgba(67,233,123,.3); color: var(--accent2); }
        .toast-error   { background: rgba(246,79,89,.12); border: 1px solid rgba(246,79,89,.3); color: var(--accent4); }
        @keyframes slideIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }

        /* KPI */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 32px;
        }
        .kpi-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px 20px;
            position: relative;
            overflow: hidden;
            transition: transform .25s;
        }
        .kpi-card:hover { transform: translateY(-3px); }
        .kpi-card::before { content:''; position:absolute; top:0; left:0; width:100%; height:3px; }
        .kpi-card.purple::before { background: linear-gradient(90deg,var(--accent),#a78bfa); }
        .kpi-card.green::before  { background: linear-gradient(90deg,var(--accent2),#06d6a0); }
        .kpi-card.orange::before { background: linear-gradient(90deg,var(--accent3),#ffd700); }
        .kpi-value { font-size: 2.2rem; font-weight: 800; line-height: 1; margin-bottom: 4px; }
        .kpi-card.purple .kpi-value { color: var(--accent); }
        .kpi-card.green  .kpi-value { color: var(--accent2); }
        .kpi-card.orange .kpi-value { color: var(--accent3); }
        .kpi-label { color: var(--muted); font-size: .8rem; font-weight: 500; }

        /* Search / Filter panel */
        .filter-panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px 24px;
            margin-bottom: 28px;
        }
        .filter-panel form {
            display: grid;
            grid-template-columns: 1fr auto auto auto auto;
            gap: 12px;
            align-items: end;
        }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: .78rem; color: var(--muted); font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
        .form-control {
            background: #F4F6F8;
            border: 1px solid #D5D8DC;
            color: var(--text);
            border-radius: 8px;
            padding: 9px 14px;
            font-size: .875rem;
            font-family: inherit;
            outline: none;
            transition: border-color .2s;
            width: 100%;
        }
        .form-control:focus { border-color: var(--accent2); }
        .form-control option { background: #fff; }
        .btn-search {
            background: var(--accent2);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 22px;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: opacity .2s;
            font-family: inherit;
        }
        .btn-search:hover { opacity: .85; }
        .btn-reset {
            background: #E8ECF0;
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 9px 18px;
            font-size: .875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: all .2s;
            font-family: inherit;
        }
        .btn-reset:hover { color: var(--text); }

        .results-count {
            margin-bottom: 18px;
            font-size: .875rem;
            color: var(--muted);
        }
        .results-count strong { color: var(--accent2); }

        /* Table */
        .table-wrap {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #F4F6F8; }
        th {
            padding: 14px 18px;
            text-align: left;
            font-size: .78rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        td { padding: 14px 18px; border-bottom: 1px solid var(--border); font-size: .875rem; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr { transition: background .15s; }
        tbody tr:hover { background: #F8FAFB; }

        .post-title-link {
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 320px;
        }
        .post-title-link:hover { color: var(--accent2); }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: .76rem;
            font-weight: 600;
        }
        .chip-replies { background: rgba(52,73,94,.12); color: var(--accent); }
        .chip-views   { background: rgba(46,204,113,.12); color: #27AE60; }

        .action-btns { display: flex; gap: 8px; flex-wrap: wrap; }
        .ab {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: .78rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .2s;
            display: inline-block;
            font-family: inherit;
        }
        .ab:hover { opacity: .8; }
        .ab-view   { background: rgba(52,73,94,.10);  color: var(--accent); }
        .ab-edit   { background: rgba(243,156,18,.15); color: #D68910; }
        .ab-delete { background: rgba(231,76,60,.12);  color: var(--accent4); }

        /* Replies expand */
        .replies-toggle {
            background: none;
            border: none;
            color: var(--muted);
            font-size: .78rem;
            cursor: pointer;
            padding: 4px 0;
            font-family: inherit;
            text-decoration: underline;
            transition: color .2s;
        }
        .replies-toggle:hover { color: var(--text); }

        .replies-row { display: none; }
        .replies-row.open { display: table-row; }

        .replies-wrap {
            padding: 16px 22px;
            background: #F8FAFB;
            border-left: 3px solid var(--accent2);
        }
        .reply-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }
        .reply-item:last-child { border-bottom: none; }
        .reply-meta { color: var(--muted); font-size: .78rem; white-space: nowrap; min-width: 120px; }
        .reply-content { flex: 1; font-size: .85rem; color: var(--text); }
        .reply-actions { display: flex; gap: 6px; flex-shrink: 0; }

        /* Empty */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }
        .empty-state .emoji { font-size: 3rem; margin-bottom: 12px; }

        /* Responsive */
        @media (max-width: 900px) {
            .filter-panel form { grid-template-columns: 1fr 1fr; }
            .filter-panel form .btn-search,
            .filter-panel form .btn-reset { grid-column: span 1; }
        }
        @media (max-width: 600px) {
            .topbar { flex-direction: column; gap: 12px; }
            .filter-panel form { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<nav class="topbar">
    <div class="topbar-brand">🛠️ CityZen Admin</div>
    <div class="topbar-nav">
        <a href="<?= htmlspecialchars(forum_admin_nav_base()) ?>" class="nav-btn active">🏠 Dashboard</a>
        <a href="<?= htmlspecialchars(forum_admin_nav_base()) ?>?page=statistics" class="nav-btn">📈 Statistiques</a>
        <a href="<?= htmlspecialchars(forum_list_url('page=home')) ?>" class="nav-btn">👁️ Forum</a>
    </div>
</nav>

<div class="page">

    <?php if (!empty($message)) echo $message; ?>

    <!-- KPI -->
    <div class="kpi-grid">
        <div class="kpi-card purple">
            <div class="kpi-value"><?= $totalPosts ?></div>
            <div class="kpi-label">💬 Discussions</div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-value"><?= $totalReplies ?></div>
            <div class="kpi-label">↩️ Réponses</div>
        </div>
        <div class="kpi-card orange">
            <div class="kpi-value"><?= $totalLikes ?></div>
            <div class="kpi-label">👍 Likes</div>
        </div>
        <div class="kpi-card purple">
            <div class="kpi-value"><?= $totalOpenReports ?></div>
            <div class="kpi-label">🚩 Signalements ouverts</div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-value"><?= $activeEmailSubs ?></div>
            <div class="kpi-label">📧 Abonnements email</div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════
         ░░░ MODÉRATION AVANCÉE - SIGNALEMENTS ░░░
         ═════════════════════════════════════════════════════════════════ -->
    
    <div class="table-wrap" style="margin-bottom:28px;">
        <div style="padding:14px 18px;font-weight:700;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div>🛡️ Modération avancée — Signalements</div>
            <div style="font-size:.75rem;color:var(--muted);font-weight:500;">Total: <strong style="color:var(--accent4);"><?= $totalOpenReports ?></strong></div>
        </div>

        <?php if ($totalOpenReports === 0): ?>
            <div class="empty-state" style="padding:40px 20px;">
                <div class="emoji" style="font-size:4rem;margin-bottom:16px;">✨</div>
                <p style="font-size:1rem;color:var(--accent2);font-weight:600;">Excellent ! Aucun signalement en attente.</p>
                <p style="font-size:.85rem;color:var(--muted);margin-top:6px;">Votre forum est bien modéré.</p>
            </div>
        <?php else: ?>
            <!-- Stats des signalements -->
            <div style="padding:16px 18px;border-bottom:1px solid var(--border);display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;">
                <div style="background:#FEF3E2;border-radius:8px;padding:12px;border-left:4px solid var(--accent3);">
                    <div style="font-size:.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:4px;">Total signalements</div>
                    <div style="font-size:1.5rem;font-weight:800;color:var(--accent3);"><?= $totalOpenReports ?></div>
                </div>
                <div style="background:#E3F2FD;border-radius:8px;padding:12px;border-left:4px solid var(--accent);">
                    <div style="font-size:.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:4px;">Posts signalés</div>
                    <div style="font-size:1.5rem;font-weight:800;color:var(--accent);"><?= count($reportsByPost) ?></div>
                </div>
                <div style="background:#F0F4F8;border-radius:8px;padding:12px;border-left:4px solid var(--muted);">
                    <div style="font-size:.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:4px;">Raison majeure</div>
                    <div style="font-size:.95rem;font-weight:600;color:var(--text);">
                        <?= !empty($topReasons) ? htmlspecialchars((string)($topReasons[0]['reason'] ?? '')) : '—' ?>
                    </div>
                </div>
            </div>

            <!-- Posts signalés - Vue détaillée -->
            <div style="padding:16px 18px;">
                <?php foreach ($reportsByPost as $postId => $post): ?>
                    <div style="border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:14px;background:#F8FAFB;">
                        <!-- En-tête du post -->
                        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px;">
                            <div style="flex:1;">
                                <div style="font-weight:700;color:var(--text);font-size:.95rem;margin-bottom:4px;">
                                    <a href="<?= htmlspecialchars(forum_post_url($postId)) ?>" style="color:var(--accent2);text-decoration:none;">
                                        #<?= $postId ?> — <?= htmlspecialchars(substr((string)$post['title'], 0, 60)) ?>
                                    </a>
                                </div>
                                <div style="font-size:.78rem;color:var(--muted);">
                                    👤 Auteur #<?= $post['author_id'] ?> • 
                                    👁️ <?= $post['view_count'] ?> vues • 
                                    <span style="padding:2px 6px;background:<?= $post['post_status'] === 'Masqué' ? 'rgba(231,76,60,.15)' : 'rgba(46,204,113,.15)' ?>;border-radius:4px;color:<?= $post['post_status'] === 'Masqué' ? 'var(--accent4)' : 'var(--accent2)' ?>;font-size:.7rem;font-weight:600;">
                                        <?= htmlspecialchars((string)$post['post_status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div style="font-size:1.8rem;font-weight:800;color:var(--accent4);"><?= $post['report_count'] ?></div>
                                <div style="font-size:.7rem;color:var(--muted);text-transform:uppercase;font-weight:600;">Signalements</div>
                            </div>
                        </div>

                        <!-- Extrait du contenu -->
                        <div style="padding:10px 12px;background:#FFFFFF;border-radius:6px;border-left:3px solid var(--accent3);margin-bottom:12px;font-size:.8rem;color:var(--text);line-height:1.4;">
                            <?= htmlspecialchars(substr((string)$post['content'], 0, 150)) ?><?= strlen((string)$post['content']) > 150 ? '...' : '' ?>
                        </div>

                        <!-- Motifs de signalement -->
                        <div style="margin-bottom:12px;">
                            <div style="font-size:.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;margin-bottom:6px;">Raisons rapportées :</div>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                <?php 
                                $reasons = [];
                                foreach ($post['reports'] as $report) {
                                    $reason = htmlspecialchars((string)$report['reason']);
                                    if (!isset($reasons[$reason])) {
                                        $reasons[$reason] = 0;
                                    }
                                    $reasons[$reason]++;
                                }
                                foreach ($reasons as $reason => $count): 
                                ?>
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:#FFFFFF;border:1px solid var(--border);border-radius:20px;font-size:.75rem;font-weight:600;color:var(--text);">
                                        🏷️ <?= $reason ?> <span style="background:var(--accent4);color:#fff;padding:0 6px;border-radius:10px;font-size:.7rem;font-weight:700;margin-left:2px;"><?= $count ?></span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Signalements individuels -->
                        <div style="margin-bottom:12px;">
                            <button onclick="toggleReportDetails(<?= $postId ?>)" style="background:none;border:none;color:var(--accent);font-size:.75rem;font-weight:600;cursor:pointer;text-decoration:underline;font-family:inherit;">
                                📋 Afficher les <?= count($post['reports']) ?> signalement<?= count($post['reports']) > 1 ? 's' : '' ?> →
                            </button>
                            <div id="reports-<?= $postId ?>" style="display:none;margin-top:8px;padding:10px;background:#FFFFFF;border-radius:6px;border:1px solid var(--border);font-size:.8rem;">
                                <?php foreach ($post['reports'] as $idx => $report): ?>
                                    <div style="padding:6px 0;border-bottom:<?= $idx < count($post['reports']) - 1 ? '1px solid var(--border)' : 'none' ?>;">
                                        <div style="color:var(--muted);font-size:.75rem;">👤 #<?= $report['reporter_user_id'] ?> • 📅 <?= date('d/m/Y H:i', strtotime((string)$report['created_at'])) ?></div>
                                        <div style="color:var(--text);margin-top:2px;">📌 <?= htmlspecialchars((string)$report['reason']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div style="display:flex;gap:8px;flex-wrap:wrap;border-top:1px solid var(--border);padding-top:12px;">
                            <a href="<?= htmlspecialchars(forum_post_url($postId)) ?>" class="ab ab-view" style="background:rgba(52,73,94,.1);padding:8px 14px;border-radius:6px;font-size:.8rem;font-weight:600;text-decoration:none;color:var(--accent);cursor:pointer;">👁️ Voir le post</a>
                            
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="resolve_post_reports">
                                <input type="hidden" name="item_id" value="<?= $postId ?>">
                                <button type="submit" class="ab" style="background:rgba(46,204,113,.1);padding:8px 14px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;color:var(--accent2);cursor:pointer;font-family:inherit;">✅ Rejeter les signalements</button>
                            </form>

                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="hide_post">
                                <input type="hidden" name="item_id" value="<?= $postId ?>">
                                <button type="submit" class="ab" style="background:rgba(243,156,18,.1);padding:8px 14px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;color:var(--accent3);cursor:pointer;font-family:inherit;">🙈 Masquer le post</button>
                            </form>

                            <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr ? Cette action est définitive.');">
                                <input type="hidden" name="action" value="delete_post">
                                <input type="hidden" name="item_id" value="<?= $postId ?>">
                                <button type="submit" class="ab" style="background:rgba(231,76,60,.1);padding:8px 14px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;color:var(--accent4);cursor:pointer;font-family:inherit;">🗑️ Supprimer définitivement</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <script>
                function toggleReportDetails(postId) {
                    const el = document.getElementById('reports-' + postId);
                    if (el) {
                        el.style.display = el.style.display === 'none' ? 'block' : 'none';
                    }
                }
                function toggleReportDetails(postId) {
                    const el = document.getElementById('reports-' + postId);
                    if (el) {
                        el.style.display = el.style.display === 'none' ? 'block' : 'none';
                    }
                }
            </script>
        <?php endif; ?>
    </div>

    <div class="table-wrap" style="margin-bottom:20px;">
        <div style="padding:14px 18px;font-weight:700;border-bottom:1px solid var(--border);">🤝 Relation utilisateurs — derniers abonnements email</div>
        <?php if (empty($recentEmailSubs)): ?>
            <div class="empty-state" style="padding:20px;">Aucun abonnement email actif.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Email</th><th>Post</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentEmailSubs as $sub): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$sub['email']) ?></td>
                            <td><a class="post-title-link" href="<?= htmlspecialchars(forum_post_url((int)$sub['post_id'])) ?>">Post #<?= (int)$sub['post_id'] ?></a></td>
                            <td><?= date('d/m/Y H:i', strtotime((string)$sub['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Filter Panel -->
    <div class="filter-panel">
        <form method="GET" action="<?= htmlspecialchars(forum_admin_nav_base()) ?>">
            <div class="form-group" style="grid-column:1">
                <label for="q">🔍 Recherche</label>
                <input class="form-control" type="text" id="q" name="q"
                       placeholder="Titre ou contenu…"
                       value="<?= htmlspecialchars($keyword) ?>">
            </div>

            <div class="form-group">
                <label for="date_from">📅 Du</label>
                <input class="form-control" type="date" id="date_from" name="date_from"
                       value="<?= htmlspecialchars($dateFrom) ?>">
            </div>

            <div class="form-group">
                <label for="date_to">📅 Au</label>
                <input class="form-control" type="date" id="date_to" name="date_to"
                       value="<?= htmlspecialchars($dateTo) ?>">
            </div>

            <div class="form-group">
                <label for="sort">⬆️ Trier par</label>
                <select class="form-control" id="sort" name="sort">
                    <option value="created_at" <?= $sortBy === 'created_at' ? 'selected' : '' ?>>Date</option>
                    <option value="view_count"  <?= $sortBy === 'view_count'  ? 'selected' : '' ?>>Vues</option>
                    <option value="title"       <?= $sortBy === 'title'       ? 'selected' : '' ?>>Titre A-Z</option>
                </select>
            </div>

            <div class="form-group">
                <label for="order">↕️ Ordre</label>
                <select class="form-control" id="order" name="order">
                    <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Décroissant</option>
                    <option value="ASC"  <?= $order === 'ASC'  ? 'selected' : '' ?>>Croissant</option>
                </select>
            </div>

            <button type="submit" class="btn-search">Filtrer</button>
            <a href="<?= htmlspecialchars(forum_admin_nav_base()) ?>?page=dashboard" class="btn-reset">Réinitialiser</a>
        </form>
    </div>

    <!-- Results count -->
    <p class="results-count">
        <strong><?= count($posts) ?></strong>
        discussion<?= count($posts) > 1 ? 's' : '' ?> trouvée<?= count($posts) > 1 ? 's' : '' ?>
        <?php if ($keyword !== ''): ?>
            pour <em>"<?= htmlspecialchars($keyword) ?>"</em>
        <?php endif; ?>
    </p>

    <!-- Table -->
    <div class="table-wrap">
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <div class="emoji">🔍</div>
                <p>Aucune discussion ne correspond à vos critères.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Date</th>
                        <th>Réponses</th>
                        <th>Vues</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post):
                        $pid        = $post->getId();
                        $replyCount = $controller->countRepliesByPost($pid);
                        $replies    = $controller->listRepliesByPost($pid);
                    ?>
                        <!-- Post row -->
                        <tr>
                            <td>
                                <a href="<?= htmlspecialchars(forum_post_url($pid)) ?>"
                                   class="post-title-link">
                                    <?= htmlspecialchars($post->getTitle()) ?>
                                </a>
                                <?php if ($replyCount > 0): ?>
                                    <br>
                                    <button class="replies-toggle"
                                            onclick="toggleReplies(<?= $pid ?>)">
                                        💬 <?= $replyCount ?> réponse<?= $replyCount > 1 ? 's' : '' ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--muted);white-space:nowrap">
                                👤 #<?= $post->getUserId() ?>
                            </td>
                            <td style="color:var(--muted);white-space:nowrap;font-size:.78rem">
                                <?= date('d/m/Y', strtotime($post->getCreatedAt())) ?><br>
                                <span style="font-size:.72rem"><?= date('H:i', strtotime($post->getCreatedAt())) ?></span>
                            </td>
                            <td>
                                <span class="chip chip-replies">💬 <?= $replyCount ?></span>
                            </td>
                            <td>
                                <span class="chip chip-views">👁️ <?= $post->getViewCount() ?></span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="<?= htmlspecialchars(forum_post_url($pid)) ?>"
                                       class="ab ab-view">Voir</a>
                                    <a href="<?= htmlspecialchars(forum_admin_nav_base()) ?>?page=edit_post&id=<?= $pid ?>"
                                       class="ab ab-edit">Modifier</a>
                                    <form method="POST" action="<?= htmlspecialchars(forum_admin_nav_base()) ?>" style="display:inline;">
                                        <input type="hidden" name="action" value="<?= (method_exists($post, 'getIsFeatured') && (int)$post->getIsFeatured() === 1) ? 'unfeature_post' : 'feature_post' ?>">
                                        <input type="hidden" name="item_id" value="<?= $pid ?>">
                                        <button type="submit" class="ab ab-view"><?= (method_exists($post, 'getIsFeatured') && (int)$post->getIsFeatured() === 1) ? 'Retirer avant' : 'Mettre avant' ?></button>
                                    </form>
                                    <form method="POST" action="<?= htmlspecialchars(forum_admin_nav_base()) ?>" style="display:inline"
                                          onsubmit="return confirm('Supprimer cette discussion ?')">
                                        <input type="hidden" name="action" value="delete_post">
                                        <input type="hidden" name="item_id" value="<?= $pid ?>">
                                        <button type="submit" class="ab ab-delete">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Replies expand row -->
                        <?php if (!empty($replies)): ?>
                            <tr class="replies-row" id="replies-<?= $pid ?>">
                                <td colspan="6">
                                    <div class="replies-wrap">
                                        <?php foreach ($replies as $reply): ?>
                                            <div class="reply-item">
                                                <div class="reply-meta">
                                                    👤 #<?= $reply->getUserId() ?><br>
                                                    <?= date('d/m/Y H:i', strtotime($reply->getCreatedAt())) ?>
                                                </div>
                                                <div class="reply-content">
                                                    <?= htmlspecialchars(mb_substr($reply->getContent(), 0, 200)) ?>
                                                    <?= mb_strlen($reply->getContent()) > 200 ? '…' : '' ?>
                                                </div>
                                                <div class="reply-actions">
                                                    <a href="<?= htmlspecialchars(forum_admin_nav_base()) ?>?page=edit_reply&id=<?= $reply->getId() ?>&post_id=<?= $pid ?>"
                                                       class="ab ab-edit">Modifier</a>
                                                    <form method="POST" action="<?= htmlspecialchars(forum_admin_nav_base()) ?>" style="display:inline"
                                                          onsubmit="return confirm('Supprimer cette réponse ?')">
                                                        <input type="hidden" name="action" value="delete_reply">
                                                        <input type="hidden" name="item_id" value="<?= $reply->getId() ?>">
                                                        <button type="submit" class="ab ab-delete">Supprimer</button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div><!-- /table-wrap -->

</div><!-- /page -->

<script>
function toggleReplies(pid) {
    const row = document.getElementById('replies-' + pid);
    if (!row) return;
    row.classList.toggle('open');
}
</script>
</body>
</html>
