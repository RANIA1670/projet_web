<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/forum_store.php';

cityzen_require_agent();

$flash = '';
$flashType = 'ok';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $flash = 'Jeton de sécurité invalide.';
        $flashType = 'err';
    } else {
        if (isset($_POST['create_category'])) {
            $r = cityzen_forum_create_category((string) ($_POST['name'] ?? ''), (string) ($_POST['description'] ?? ''));
            $flash = ($r['ok'] ?? false) ? 'Catégorie créée.' : (string) ($r['error'] ?? 'Erreur création catégorie.');
            $flashType = ($r['ok'] ?? false) ? 'ok' : 'err';
        } elseif (isset($_POST['update_category'])) {
            $r = cityzen_forum_update_category((int) ($_POST['id'] ?? 0), (string) ($_POST['name'] ?? ''), (string) ($_POST['description'] ?? ''));
            $flash = ($r['ok'] ?? false) ? 'Catégorie mise à jour.' : (string) ($r['error'] ?? 'Erreur mise à jour catégorie.');
            $flashType = ($r['ok'] ?? false) ? 'ok' : 'err';
        } elseif (isset($_POST['delete_category'])) {
            $r = cityzen_forum_delete_category((int) ($_POST['id'] ?? 0));
            $flash = ($r['ok'] ?? false) ? 'Catégorie supprimée.' : (string) ($r['error'] ?? 'Suppression impossible.');
            $flashType = ($r['ok'] ?? false) ? 'ok' : 'err';
        } elseif (isset($_POST['delete_post'])) {
            $ok = cityzen_forum_delete_post_admin((int) ($_POST['id'] ?? 0));
            $flash = $ok ? 'Post supprimé.' : 'Post introuvable.';
            $flashType = $ok ? 'ok' : 'err';
        } elseif (isset($_POST['delete_reply'])) {
            $ok = cityzen_forum_delete_reply_admin((int) ($_POST['id'] ?? 0));
            $flash = $ok ? 'Réponse supprimée.' : 'Réponse introuvable.';
            $flashType = $ok ? 'ok' : 'err';
        }
    }
}

$categories = cityzen_forum_categories_with_count();
$posts = cityzen_forum_posts(null, 100);
$selectedPostId = isset($_GET['post']) ? (int) $_GET['post'] : 0;
$replies = $selectedPostId > 0 ? cityzen_forum_replies($selectedPostId) : [];

cityzen_render_head('Gestion forum', [cityzen_asset('assets/css/forum.css')]);
?>
<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-brand"><span>City<strong>Zen</strong></span></div>
    <nav class="sidebar-nav">
      <?php foreach ($cityzen['admin_menu'] as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= ($item['key'] ?? '') === 'forum' ? 'is-active' : '' ?>">
          <span class="nav-bullet"></span><?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>
  <main class="admin-page">
    <header class="admin-header">
      <div>
        <h1>Gestion forum</h1>
        <p class="admin-header-lead">Catégories, posts et réponses.</p>
      </div>
    </header>

    <?php if ($flash !== ''): ?><p class="<?= $flashType === 'ok' ? 'flash-ok' : 'flash-err' ?>"><?= htmlspecialchars($flash) ?></p><?php endif; ?>

    <section class="panel users-mgmt-panel">
      <h2>Catégories</h2>
      <form method="post" class="forum-form" style="max-width:520px;margin-bottom:18px">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
        <input type="hidden" name="create_category" value="1">
        <input type="text" name="name" maxlength="100" placeholder="Nom de catégorie" required>
        <textarea name="description" placeholder="Description (optionnelle)"></textarea>
        <button class="forum-btn primary" type="submit">Créer catégorie</button>
      </form>
      <?php foreach ($categories as $cat): ?>
        <form method="post" class="forum-form forum-panel" style="margin-bottom:10px">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
          <input type="text" name="name" value="<?= htmlspecialchars((string) $cat['name']) ?>" maxlength="100" required>
          <textarea name="description"><?= htmlspecialchars((string) ($cat['description'] ?? '')) ?></textarea>
          <p class="forum-meta">Posts liés: <?= (int) $cat['posts_count'] ?></p>
          <div class="forum-actions">
            <button class="forum-btn" type="submit" name="update_category" value="1">Enregistrer</button>
            <button class="forum-btn" type="submit" name="delete_category" value="1" onclick="return confirm('Supprimer cette catégorie ?');">Supprimer</button>
          </div>
        </form>
      <?php endforeach; ?>
    </section>

    <section class="panel users-mgmt-panel">
      <h2>Posts</h2>
      <div class="forum-post-list">
        <?php foreach ($posts as $post): ?>
          <article class="forum-post-item">
            <h3><?= htmlspecialchars((string) $post['title']) ?></h3>
            <p class="forum-meta">#<?= (int) $post['id'] ?> — <?= htmlspecialchars((string) $post['category_name']) ?> — <?= htmlspecialchars((string) $post['username']) ?> — <?= htmlspecialchars((string) $post['created_at']) ?></p>
            <div class="forum-actions">
              <a class="forum-btn" href="<?= htmlspecialchars(cityzen_asset('forum/post.php?id=' . (int) $post['id'])) ?>" target="_blank" rel="noopener">Voir</a>
              <a class="forum-btn" href="<?= htmlspecialchars(cityzen_asset('admin/forum.php?post=' . (int) $post['id'])) ?>">Réponses</a>
              <form method="post" style="display:inline">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                <button class="forum-btn" type="submit" name="delete_post" value="1" onclick="return confirm('Supprimer ce post et ses réponses ?');">Supprimer</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if ($selectedPostId > 0): ?>
      <section class="panel users-mgmt-panel">
        <h2>Réponses du post #<?= $selectedPostId ?></h2>
        <div class="forum-replies">
          <?php foreach ($replies as $reply): ?>
            <article class="forum-reply">
              <p class="forum-meta">#<?= (int) $reply['id'] ?> — <?= htmlspecialchars((string) $reply['username']) ?> — <?= htmlspecialchars((string) $reply['created_at']) ?></p>
              <p><?= nl2br(htmlspecialchars((string) $reply['content'])) ?></p>
              <form method="post">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int) $reply['id'] ?>">
                <button class="forum-btn" type="submit" name="delete_reply" value="1" onclick="return confirm('Supprimer cette réponse ?');">Supprimer</button>
              </form>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </main>
</div>
<?php cityzen_render_footer(); ?>
