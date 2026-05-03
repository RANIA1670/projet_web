<?php
/**
 * Vue Back-Office : Tableau de bord de modération
 */

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

if ($currentUserId === 0 || !$isAdmin) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../controllers/ForumController.php';
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

// ── Stats rapides ────────────────────────────────────────────
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
            --radius: 14px;
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
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .topbar-nav { display: flex; gap: 10px; }
        .nav-btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            color: var(--muted);
            border: 1px solid var(--border);
            transition: all .2s;
        }
        .nav-btn:hover { background: var(--surface2); color: var(--text); }
        .nav-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }

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
            background: var(--surface);
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
            background: var(--surface);
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
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 8px;
            padding: 9px 14px;
            font-size: .875rem;
            font-family: inherit;
            outline: none;
            transition: border-color .2s;
            width: 100%;
        }
        .form-control:focus { border-color: var(--accent); }
        .form-control option { background: var(--surface2); }
        .btn-search {
            background: var(--accent);
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
            background: var(--surface2);
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
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--surface2); }
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
        tbody tr:hover { background: var(--surface2); }

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
        .chip-replies { background: rgba(108,99,255,.15); color: var(--accent); }
        .chip-views   { background: rgba(67,233,123,.10); color: var(--accent2); }

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
        .ab-view   { background: rgba(108,99,255,.2); color: var(--accent); }
        .ab-edit   { background: rgba(247,151,30,.2); color: var(--accent3); }
        .ab-delete { background: rgba(246,79,89,.2);  color: var(--accent4); }

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
            background: rgba(0,0,0,.2);
            border-left: 3px solid var(--accent);
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
        <a href="dashboard.php" class="nav-btn active">🏠 Dashboard</a>
        <a href="statistics.php" class="nav-btn">📈 Statistiques</a>
        <a href="../front_office/list_posts.php" class="nav-btn">👁️ Forum</a>
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
    </div>

    <!-- Filter Panel -->
    <div class="filter-panel">
        <form method="GET" action="dashboard.php">
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
            <a href="dashboard.php" class="btn-reset">Réinitialiser</a>
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
                                <a href="../front_office/view_post.php?id=<?= $pid ?>"
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
                                    <a href="../front_office/view_post.php?id=<?= $pid ?>"
                                       class="ab ab-view">Voir</a>
                                    <a href="edit_post.php?id=<?= $pid ?>"
                                       class="ab ab-edit">Modifier</a>
                                    <form method="POST" style="display:inline"
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
                                                    <a href="edit_reply.php?id=<?= $reply->getId() ?>&post_id=<?= $pid ?>"
                                                       class="ab ab-edit">Modifier</a>
                                                    <form method="POST" style="display:inline"
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
