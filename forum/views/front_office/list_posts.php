<?php
/**
 * Vue Front-Office : Liste des posts du forum avec recherche & filtres
 */

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

require_once __DIR__ . '/../../config/ForumRedirect.php';
require_once __DIR__ . '/../../controllers/ForumController.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Favorite.php';
$controller = new ForumController();

// Charge les discussions favorites de l'utilisateur connecté
$favoritePosts = [];
if ($currentUserId > 0) {
    $favPostIds = Favorite::findPostIdsByUserId($currentUserId);
    foreach ($favPostIds as $fid) {
        $fp = Post::findById((int)$fid);
        if ($fp) $favoritePosts[] = $fp;
    }
}

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

usort($posts, static function ($a, $b) {
    $fa = method_exists($a, 'getIsFeatured') ? (int)$a->getIsFeatured() : 0;
    $fb = method_exists($b, 'getIsFeatured') ? (int)$b->getIsFeatured() : 0;
    if ($fa !== $fb) {
        return $fb <=> $fa;
    }
    return strcmp((string)$b->getCreatedAt(), (string)$a->getCreatedAt());
});

$hasFilter = ($keyword !== '' || $dateFrom !== '' || $dateTo !== '');
$showCreated = isset($_GET['created']);
require_once __DIR__ . '/../../inc/public_shell.php';
cityzen_forum_public_shell_start('Forum — Discussions');
?>
<style>
        :root {
            --bg: #F4F6F8;
            --surface: #FFFFFF;
            --surface2: #EEF1F4;
            --accent: #2ECC71;
            --accent2: #F39C12;
            --accent3: #F39C12;
            --navy: #34495E;
            --text: #2C3E50;
            --muted: #7F8C8D;
            --sidebar: #2F3C4F;
            --link-muted: #9BA4B5;
            --border: #E8ECF0;
            --radius: 10px;
            --glow: 0 4px 20px rgba(46, 204, 113, 0.15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* ── Hero ── */
        .hero {
            background: var(--sidebar);
            border-bottom: 1px solid rgba(0,0,0,.12);
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
            background: radial-gradient(circle, rgba(46, 204, 113, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            margin-bottom: 14px;
            color: #FFFFFF;
            position: relative;
        }
        .hero p {
            color: var(--link-muted);
            font-size: 1.05rem;
            max-width: 540px;
            margin: 0 auto 28px;
            line-height: 1.6;
            position: relative;
        }
        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 28px;
            background: var(--accent);
            color: #fff;
            text-decoration: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: .95rem;
            transition: opacity .2s, transform .2s;
            box-shadow: 0 4px 18px rgba(46, 204, 113, 0.35);
            position: relative;
        }
        .hero-cta:hover { opacity: .92; transform: translateY(-2px); }

        /* ── Barre d’action (CTA visible au défilement) ── */
        .forum-toolbar {
            position: sticky;
            top: 0;
            z-index: 40;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.06);
        }
        .forum-toolbar-inner {
            max-width: 1060px;
            margin: 0 auto;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .forum-toolbar-title {
            font-weight: 700;
            font-size: 1rem;
            color: var(--navy);
        }
        .forum-toolbar-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: var(--accent);
            color: #fff;
            text-decoration: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: .9rem;
            transition: opacity .2s, transform .2s;
            box-shadow: 0 3px 14px rgba(46, 204, 113, 0.3);
        }
        .forum-toolbar-cta:hover {
            opacity: .92;
            transform: translateY(-1px);
        }

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
        .fc:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2); }
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
        .btn-ghost:hover { color: var(--text); border-color: var(--accent); }

        /* ── Results info bar ── */
        .info-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: .875rem;
        }
        .result-count { color: var(--muted); }
        .result-count strong { color: var(--accent); font-weight: 700; }
        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(46, 204, 113, 0.12);
            color: var(--navy);
            border: 1px solid rgba(46, 204, 113, 0.28);
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
            background: linear-gradient(180deg, var(--accent), var(--navy));
            opacity: 0;
            transition: opacity .25s;
        }
        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--glow);
            border-color: rgba(46, 204, 113, 0.35);
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
            background: rgba(52, 73, 94, 0.08);
            color: var(--navy);
            border: 1px solid rgba(52, 73, 94, 0.2);
            border-radius: 8px;
            text-decoration: none;
            font-size: .8rem;
            font-weight: 600;
            transition: all .2s;
        }
        .read-btn:hover {
            background: var(--navy);
            color: #fff;
            border-color: var(--navy);
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

        .flash-success {
            background: rgba(46, 204, 113, 0.12);
            border: 1px solid rgba(46, 204, 113, 0.35);
            color: var(--text);
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        /* ── Favourites section ── */
        .fav-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 24px;
            margin-bottom: 28px;
        }
        .fav-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .fav-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .fav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 14px;
            background: var(--surface2);
            border-radius: 8px;
            border: 1px solid var(--border);
            transition: background .2s;
        }
        .fav-item:hover { background: #e4f7ec; }
        .fav-item-title {
            font-size: .88rem;
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
            flex: 1;
        }
        .fav-item-title:hover { color: var(--accent); }
        .fav-item-meta { font-size: .75rem; color: var(--muted); white-space: nowrap; }
    </style>

<!-- Hero -->
<header class="hero">
    <h1>🏘️ Forum CityZen</h1>
    <p>Échangez vos idées, participez aux discussions et contribuez à votre communauté.</p>
    <a href="<?= htmlspecialchars(forum_front_url('page=create')) ?>" class="hero-cta">✍️ Lancer une discussion</a>
</header>

<div class="forum-toolbar" role="navigation" aria-label="Actions forum">
    <div class="forum-toolbar-inner">
        <span class="forum-toolbar-title">💬 Discussions de la communauté</span>
    </div>
</div>

<div class="forum-inner">

    <?php if ($showCreated): ?>
        <div class="flash-success" role="status">✅ Votre discussion a été publiée.</div>
    <?php endif; ?>

    <?php if ($currentUserId > 0 && !empty($favoritePosts)): ?>
    <section class="fav-section" aria-label="Discussions favorites">
        <div class="fav-section-title">⭐ Mes discussions favorites</div>
        <div class="fav-list">
            <?php foreach ($favoritePosts as $fp): ?>
            <div class="fav-item">
                <a href="<?= htmlspecialchars(forum_post_url($fp->getId())) ?>" class="fav-item-title">
                    <?= htmlspecialchars($fp->getTitle()) ?>
                </a>
                <span class="fav-item-meta">📅 <?= date('d/m/Y', strtotime($fp->getCreatedAt())) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($keyword !== ''): ?>
        <div style="background: #e8f4f8; border: 1px solid #b3d9e6; padding: 12px; margin-bottom: 20px; border-radius: 6px; font-size: .9rem;">
            Recherche : <strong><?= htmlspecialchars($keyword) ?></strong> — <?= count($posts) ?> résultat<?= count($posts) !== 1 ? 's' : '' ?>
        </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="">
            <input type="hidden" name="page" value="home">
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
                <a href="<?= htmlspecialchars(forum_front_url('page=home')) ?>" class="btn-ghost">✕ Effacer</a>
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
            <?php
            $kwShow = function_exists('mb_substr')
                ? mb_substr($keyword, 0, 30, 'UTF-8')
                : substr($keyword, 0, 30);
            ?>
            <span class="filter-tag">🔍 "<?= htmlspecialchars($kwShow) ?>"</span>
        <?php elseif ($dateFrom !== '' || $dateTo !== ''): ?>
            <span class="filter-tag">📅 Filtre par date actif</span>
        <?php endif; ?>
    </div>

    <!-- Posts list -->
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <div class="big-emoji">🔍</div>
            <h2>Aucune discussion trouvée</h2>
            <p>Essayez avec d'autres mots-clés ou <a href="<?= htmlspecialchars(forum_front_url('page=home')) ?>" style="color:var(--accent)">réinitialisez les filtres</a>.</p>
            <a href="<?= htmlspecialchars(forum_front_url('page=create')) ?>" class="hero-cta" style="font-size:.9rem">✍️ Créer la première discussion</a>
        </div>
    <?php else: ?>
        <div class="posts-list">
            <?php foreach ($posts as $post):
                $replyCount = $controller->countRepliesByPost($post->getId());
                $likeCount  = $controller->countPostLikes($post->getId());
            ?>
                <article class="post-card">
                    <div class="card-header">
                        <?php if (method_exists($post, 'getIsFeatured') && (int)$post->getIsFeatured() === 1): ?>
                            <span class="filter-tag" style="margin-right:8px;">📌 Mis en avant</span>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars(forum_post_url($post->getId())) ?>" class="post-title">
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
                        <a href="<?= htmlspecialchars(forum_post_url($post->getId())) ?>" class="read-btn">
                            Lire la suite →
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
<?php
cityzen_forum_public_shell_end();
