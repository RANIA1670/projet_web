<?php
/**
 * Vue Front-Office : Liste des posts du forum avec recherche & filtres
 */

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

require_once __DIR__ . '/../../controllers/ForumController.php';
$controller = new ForumController();

// ── Recherche / Filtrage ──────────────────────────────────────
$keyword  = trim((string)($_GET['q']        ?? ''));
$dateFrom = trim((string)($_GET['date_from'] ?? ''));
$dateTo   = trim((string)($_GET['date_to']   ?? ''));
$sortBy   = in_array($_GET['sort'] ?? '', ['created_at','view_count','title']) ? $_GET['sort'] : 'created_at';
$order    = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

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

$hasFilter = ($keyword !== '' || $dateFrom !== '' || $dateTo !== '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum CityZen — Discussions</title>
    <meta name="description" content="Participez aux discussions de la communauté CityZen, échangez des idées et partagez vos expériences.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d1117;
            --surface: #161b27;
            --surface2: #1f2535;
            --accent: #4f8ef7;
            --accent2: #43e97b;
            --accent3: #f7971e;
            --text: #e6edf3;
            --muted: #7d8590;
            --border: rgba(255,255,255,0.08);
            --radius: 16px;
            --glow: 0 0 40px rgba(79,142,247,.15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, #0d1117 0%, #1a2540 50%, #0d1117 100%);
            border-bottom: 1px solid var(--border);
            padding: 60px 24px 48px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(79,142,247,.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            margin-bottom: 14px;
            background: linear-gradient(135deg, #fff 30%, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero p {
            color: var(--muted);
            font-size: 1.05rem;
            max-width: 540px;
            margin: 0 auto 28px;
            line-height: 1.6;
        }
        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 28px;
            background: linear-gradient(135deg, var(--accent), #7b6cf6);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: .95rem;
            transition: opacity .2s, transform .2s;
            box-shadow: 0 4px 24px rgba(79,142,247,.35);
        }
        .hero-cta:hover { opacity: .9; transform: translateY(-2px); }

        /* ── Layout ── */
        .page { max-width: 1060px; margin: 0 auto; padding: 36px 20px; }

        /* ── Filter Bar ── */
        .filter-bar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 24px;
            margin-bottom: 28px;
            backdrop-filter: blur(8px);
        }
        .filter-bar form {
            display: grid;
            grid-template-columns: 1fr auto auto auto auto;
            gap: 12px;
            align-items: end;
        }
        .fg { display: flex; flex-direction: column; gap: 5px; }
        .fg label {
            font-size: .72rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .fc {
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 8px;
            padding: 9px 14px;
            font-size: .875rem;
            font-family: inherit;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .fc:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,142,247,.15); }
        .fc option { background: var(--surface2); }

        .search-wrapper { position: relative; }
        .search-wrapper .fc { padding-left: 36px; }
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: .9rem;
            pointer-events: none;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 20px;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            font-family: inherit;
            transition: opacity .2s;
        }
        .btn-primary:hover { opacity: .85; }
        .btn-ghost {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 9px 16px;
            font-size: .875rem;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            text-decoration: none;
            font-family: inherit;
            transition: all .2s;
        }
        .btn-ghost:hover { color: var(--text); border-color: rgba(255,255,255,.15); }

        /* ── Results info bar ── */
        .info-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: .875rem;
        }
        .result-count { color: var(--muted); }
        .result-count strong { color: var(--accent2); font-weight: 700; }
        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(79,142,247,.12);
            color: var(--accent);
            border: 1px solid rgba(79,142,247,.25);
            border-radius: 20px;
            padding: 3px 12px;
            font-size: .78rem;
            font-weight: 600;
        }

        /* ── Post Cards ── */
        .posts-list { display: flex; flex-direction: column; gap: 16px; }

        .post-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            transition: transform .2s, box-shadow .2s, border-color .2s;
            position: relative;
            overflow: hidden;
        }
        .post-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0;
            width: 3px; height: 100%;
            background: linear-gradient(180deg, var(--accent), #7b6cf6);
            opacity: 0;
            transition: opacity .25s;
        }
        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--glow);
            border-color: rgba(79,142,247,.25);
        }
        .post-card:hover::before { opacity: 1; }

        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 12px;
        }

        .post-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text);
            text-decoration: none;
            line-height: 1.4;
            flex: 1;
            transition: color .2s;
        }
        .post-title:hover { color: var(--accent); }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: .8rem;
            color: var(--muted);
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .meta-item { display: flex; align-items: center; gap: 5px; }

        .post-excerpt {
            color: var(--muted);
            font-size: .875rem;
            line-height: 1.65;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 18px;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 10px;
        }

        .stats-row { display: flex; gap: 16px; }
        .stat {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .8rem;
            color: var(--muted);
            font-weight: 500;
        }

        .read-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            background: rgba(79,142,247,.12);
            color: var(--accent);
            border: 1px solid rgba(79,142,247,.2);
            border-radius: 8px;
            text-decoration: none;
            font-size: .8rem;
            font-weight: 600;
            transition: all .2s;
        }
        .read-btn:hover {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        /* ── Empty ── */
        .empty-state {
            text-align: center;
            padding: 80px 24px;
            color: var(--muted);
        }
        .empty-state .big-emoji { font-size: 4rem; margin-bottom: 16px; }
        .empty-state h2 { font-size: 1.4rem; font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .empty-state p { margin-bottom: 24px; font-size: .95rem; }

        /* ── Responsive ── */
        @media (max-width: 760px) {
            .filter-bar form { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 500px) {
            .filter-bar form { grid-template-columns: 1fr; }
            .hero { padding: 40px 16px 32px; }
        }
    </style>
</head>
<body>

<!-- Hero -->
<header class="hero">
    <h1>🏘️ Forum CityZen</h1>
    <p>Échangez vos idées, participez aux discussions et contribuez à votre communauté.</p>
    <a href="create_post.php" class="hero-cta">✍️ Lancer une discussion</a>
</header>

<main class="page">

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="list_posts.php">
            <div class="fg" style="grid-column:1">
                <label for="q">Recherche</label>
                <div class="search-wrapper">
                    <span class="search-icon">🔍</span>
                    <input class="fc" type="text" id="q" name="q"
                           placeholder="Mots-clés dans le titre ou le contenu…"
                           value="<?= htmlspecialchars($keyword) ?>">
                </div>
            </div>

            <div class="fg">
                <label for="date_from">Du</label>
                <input class="fc" type="date" id="date_from" name="date_from"
                       value="<?= htmlspecialchars($dateFrom) ?>">
            </div>

            <div class="fg">
                <label for="date_to">Au</label>
                <input class="fc" type="date" id="date_to" name="date_to"
                       value="<?= htmlspecialchars($dateTo) ?>">
            </div>

            <div class="fg">
                <label for="sort">Trier par</label>
                <select class="fc" id="sort" name="sort">
                    <option value="created_at" <?= $sortBy==='created_at'?'selected':'' ?>>📅 Date</option>
                    <option value="view_count"  <?= $sortBy==='view_count'?'selected':'' ?>>👁️ Vues</option>
                    <option value="title"       <?= $sortBy==='title'?'selected':'' ?>>🔤 A-Z</option>
                </select>
            </div>

            <div class="fg">
                <label for="order">Ordre</label>
                <select class="fc" id="order" name="order">
                    <option value="DESC" <?= $order==='DESC'?'selected':'' ?>>↓ Récent</option>
                    <option value="ASC"  <?= $order==='ASC'?'selected':'' ?>>↑ Ancien</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Chercher</button>
            <?php if ($hasFilter): ?>
                <a href="list_posts.php" class="btn-ghost">✕ Effacer</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Info bar -->
    <div class="info-bar">
        <span class="result-count">
            <strong><?= count($posts) ?></strong>
            discussion<?= count($posts) !== 1 ? 's' : '' ?>
            <?= $hasFilter ? 'trouvée' . (count($posts) !== 1 ? 's' : '') : '' ?>
        </span>
        <?php if ($keyword !== ''): ?>
            <span class="filter-tag">🔍 "<?= htmlspecialchars(mb_substr($keyword,0,30)) ?>"</span>
        <?php elseif ($dateFrom !== '' || $dateTo !== ''): ?>
            <span class="filter-tag">📅 Filtre par date actif</span>
        <?php endif; ?>
    </div>

    <!-- Posts list -->
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <div class="big-emoji">🔍</div>
            <h2>Aucune discussion trouvée</h2>
            <p>Essayez avec d'autres mots-clés ou <a href="list_posts.php" style="color:var(--accent)">réinitialisez les filtres</a>.</p>
            <a href="create_post.php" class="hero-cta" style="font-size:.9rem">✍️ Créer la première discussion</a>
        </div>
    <?php else: ?>
        <div class="posts-list">
            <?php foreach ($posts as $post):
                $replyCount = $controller->countRepliesByPost($post->getId());
                $likeCount  = $controller->countPostLikes($post->getId());
            ?>
                <article class="post-card">
                    <div class="card-header">
                        <a href="view_post.php?id=<?= $post->getId() ?>" class="post-title">
                            <?= htmlspecialchars($post->getTitle()) ?>
                        </a>
                    </div>

                    <div class="post-meta">
                        <span class="meta-item">👤 Utilisateur #<?= $post->getUserId() ?></span>
                        <span class="meta-item">📅 <?= date('d/m/Y à H:i', strtotime($post->getCreatedAt())) ?></span>
                    </div>

                    <p class="post-excerpt"><?= htmlspecialchars($post->getContent()) ?></p>

                    <div class="card-footer">
                        <div class="stats-row">
                            <span class="stat">👁️ <?= $post->getViewCount() ?> vue<?= $post->getViewCount() > 1 ? 's' : '' ?></span>
                            <span class="stat">💬 <?= $replyCount ?> réponse<?= $replyCount > 1 ? 's' : '' ?></span>
                            <span class="stat">👍 <?= $likeCount ?> like<?= $likeCount > 1 ? 's' : '' ?></span>
                        </div>
                        <a href="view_post.php?id=<?= $post->getId() ?>" class="read-btn">
                            Lire la suite →
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>
</body>
</html>
