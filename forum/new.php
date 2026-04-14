<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/forum_store.php';

cityzen_require_citizen_login();

$cats = cityzen_forum_categories_with_count();
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $res = cityzen_forum_create_post(
            (string) ($_POST['title'] ?? ''),
            (string) ($_POST['content'] ?? ''),
            cityzen_current_user_id(),
            (int) ($_POST['category_id'] ?? 0)
        );
        if (($res['ok'] ?? false) === true) {
            header('Location: ' . cityzen_asset('forum/post.php?id=' . (int) $res['id']), true, 303);
            exit;
        }
        $error = (string) ($res['error'] ?? 'Création impossible.');
    }
}

cityzen_render_head('Nouveau post forum', [cityzen_asset('assets/css/forum.css')]);
?>
<div class="site-shell">
  <header class="topbar topbar-public">
    <div class="brand"><span class="brand-dot"></span><span class="brand-text">City <strong>Zen</strong></span></div>
    <nav class="main-nav">
      <a href="<?= htmlspecialchars(cityzen_asset('forum/index.php')) ?>" class="is-active">Forum</a>
      <a href="<?= htmlspecialchars(cityzen_asset('index.php')) ?>">Accueil</a>
    </nav>
  </header>
  <main class="page public-page">
    <h2>Nouveau sujet</h2>
    <?php if ($error !== ''): ?><p class="flash-err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" class="forum-form">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
      <label>Titre
        <input type="text" name="title" maxlength="200" required value="<?= htmlspecialchars((string) ($_POST['title'] ?? '')) ?>">
      </label>
      <label>Catégorie
        <select name="category_id" required>
          <option value="">— Choisir —</option>
          <?php foreach ($cats as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= (int) ($_POST['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Contenu
        <textarea name="content" required><?= htmlspecialchars((string) ($_POST['content'] ?? '')) ?></textarea>
      </label>
      <div class="forum-actions">
        <button class="forum-btn primary" type="submit">Publier</button>
        <a class="forum-btn" href="<?= htmlspecialchars(cityzen_asset('forum/index.php')) ?>">Annuler</a>
      </div>
    </form>
  </main>
</div>
<?php cityzen_render_footer(); ?>
