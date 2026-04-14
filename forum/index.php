<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/forum_store.php';

$activeCategory = isset($_GET['category']) ? (int) $_GET['category'] : null;
if ($activeCategory !== null && $activeCategory <= 0) {
    $activeCategory = null;
}

$flash = (string) ($_GET['msg'] ?? '');
$flashType = (string) ($_GET['type'] ?? '');

$categories = cityzen_forum_categories_with_count();
$posts = cityzen_forum_posts($activeCategory, 60);

cityzen_render_head('Forum citoyen', [cityzen_asset('assets/css/forum.css')]);
?>
<div class="site-shell">
  <header class="topbar topbar-public">
    <div class="brand"><span class="brand-dot"></span><span class="brand-text">City <strong>Zen</strong></span></div>
    <nav class="main-nav">
      <?php foreach (cityzen_full_public_nav($cityzen['public_menu']) as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= ($item['key'] ?? '') === 'forum' ? 'is-active' : '' ?>"><?= htmlspecialchars($item['label']) ?></a>
        <?php if (($item['key'] ?? '') === 'equipment' && cityzen_is_logged_in() && cityzen_current_user_id() > 0): ?>
          <a href="<?= htmlspecialchars(cityzen_asset('equipment/my-reservations.php')) ?>">Mes réservations</a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="topbar-actions"><span class="avatar avatar-success"><?= htmlspecialchars(cityzen_user_initials()) ?></span></div>
  </header>

  <main class="page public-page">
    <section class="section-header">
      <h2>Forum citoyen</h2>
      <div class="forum-actions">
        <a class="forum-btn primary" href="<?= htmlspecialchars(cityzen_asset('forum/new.php')) ?>">Nouveau post</a>
      </div>
    </section>
    <?php if ($flash !== ''): ?>
      <p class="<?= $flashType === 'ok' ? 'flash-ok' : 'flash-err' ?>"><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>
    <div class="forum-layout">
      <aside class="forum-panel">
        <h3>Catégories</h3>
        <ul class="forum-cats">
          <li><a href="<?= htmlspecialchars(cityzen_asset('forum/index.php')) ?>" class="<?= $activeCategory === null ? 'is-active' : '' ?>">Toutes</a></li>
          <?php foreach ($categories as $cat): ?>
            <li>
              <a href="<?= htmlspecialchars(cityzen_asset('forum/index.php?category=' . (int) $cat['id'])) ?>" class="<?= $activeCategory === (int) $cat['id'] ? 'is-active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?> (<?= (int) $cat['posts_count'] ?>)
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </aside>
      <section class="forum-panel">
        <div class="forum-post-list">
          <?php foreach ($posts as $post): ?>
            <article class="forum-post-item">
              <h3><a href="<?= htmlspecialchars(cityzen_asset('forum/post.php?id=' . (int) $post['id'])) ?>"><?= htmlspecialchars((string) $post['title']) ?></a></h3>
              <p class="forum-meta">
                Catégorie: <?= htmlspecialchars((string) $post['category_name']) ?> —
                Par <?= htmlspecialchars((string) $post['username']) ?> —
                <?= htmlspecialchars((string) $post['created_at']) ?> —
                Réponses: <?= (int) $post['replies_count'] ?>
              </p>
              <p><?= nl2br(htmlspecialchars(mb_strimwidth((string) $post['content'], 0, 260, '…'))) ?></p>
            </article>
          <?php endforeach; ?>
          <?php if ($posts === []): ?>
            <p>Aucun post pour le moment.</p>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </main>
</div>
<?php cityzen_render_footer(); ?>
