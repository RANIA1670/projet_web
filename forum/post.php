<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/forum_store.php';

$postId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$post = $postId > 0 ? cityzen_forum_post_detail($postId) : null;
if ($post === null) {
    header('Location: ' . cityzen_asset('forum/index.php?msg=' . rawurlencode('Post introuvable') . '&type=err'), true, 302);
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['reply_submit'])) {
    cityzen_require_citizen_login();
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $error = 'Jeton de sécurité invalide.';
    } else {
        $parent = !empty($_POST['parent_reply_id']) ? (int) $_POST['parent_reply_id'] : null;
        $res = cityzen_forum_create_reply(
            $postId,
            cityzen_current_user_id(),
            (string) ($_POST['content'] ?? ''),
            $parent
        );
        if (($res['ok'] ?? false) === true) {
            header('Location: ' . cityzen_asset('forum/post.php?id=' . $postId . '&msg=' . rawurlencode('Réponse publiée') . '&type=ok'), true, 303);
            exit;
        }
        $error = (string) ($res['error'] ?? 'Réponse refusée.');
    }
}

$msg = (string) ($_GET['msg'] ?? '');
$msgType = (string) ($_GET['type'] ?? '');

$replies = cityzen_forum_replies($postId);
$byParent = [];
foreach ($replies as $r) {
    $pid = $r['parent_reply_id'] === null ? 0 : (int) $r['parent_reply_id'];
    if (!isset($byParent[$pid])) {
        $byParent[$pid] = [];
    }
    $byParent[$pid][] = $r;
}

/**
 * @param array<int, list<array<string,mixed>>> $tree
 */
function cityzen_render_replies(array $tree, int $parentId = 0, int $depth = 0): void
{
    if (!isset($tree[$parentId])) {
        return;
    }
    foreach ($tree[$parentId] as $rep) {
        $depthClass = $depth > 2 ? 2 : $depth;
        ?>
        <article class="forum-reply depth-<?= $depthClass ?>">
          <p class="forum-meta"><?= htmlspecialchars((string) $rep['username']) ?> — <?= htmlspecialchars((string) $rep['created_at']) ?></p>
          <p><?= nl2br(htmlspecialchars((string) $rep['content'])) ?></p>
          <?php if (cityzen_is_logged_in() && cityzen_current_user_id() > 0): ?>
            <form method="post" class="forum-form">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
              <input type="hidden" name="reply_submit" value="1">
              <input type="hidden" name="id" value="<?= (int) $rep['post_id'] ?>">
              <input type="hidden" name="parent_reply_id" value="<?= (int) $rep['id'] ?>">
              <textarea name="content" placeholder="Répondre à ce message..." required></textarea>
              <button class="forum-btn" type="submit">Répondre</button>
            </form>
          <?php endif; ?>
        </article>
        <?php
        cityzen_render_replies($tree, (int) $rep['id'], $depth + 1);
    }
}

cityzen_render_head((string) $post['title'], [cityzen_asset('assets/css/forum.css')]);
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
    <p><a href="<?= htmlspecialchars(cityzen_asset('forum/index.php')) ?>">← Retour forum</a></p>
    <article class="forum-panel">
      <h2><?= htmlspecialchars((string) $post['title']) ?></h2>
      <p class="forum-meta">
        Catégorie: <?= htmlspecialchars((string) $post['category_name']) ?> —
        Par <?= htmlspecialchars((string) $post['username']) ?> —
        <?= htmlspecialchars((string) $post['created_at']) ?>
      </p>
      <p><?= nl2br(htmlspecialchars((string) $post['content'])) ?></p>
    </article>

    <?php if ($msg !== ''): ?><p class="<?= $msgType === 'ok' ? 'flash-ok' : 'flash-err' ?>"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="flash-err"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <section class="forum-panel">
      <h3>Réponses</h3>
      <div class="forum-replies">
        <?php cityzen_render_replies($byParent, 0, 0); ?>
        <?php if ($replies === []): ?><p>Aucune réponse pour le moment.</p><?php endif; ?>
      </div>
    </section>

    <?php if (cityzen_is_logged_in() && cityzen_current_user_id() > 0): ?>
      <section class="forum-panel">
        <h3>Répondre au sujet</h3>
        <form method="post" class="forum-form">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
          <input type="hidden" name="reply_submit" value="1">
          <input type="hidden" name="id" value="<?= $postId ?>">
          <textarea name="content" required placeholder="Votre réponse..."></textarea>
          <button class="forum-btn primary" type="submit">Publier la réponse</button>
        </form>
      </section>
    <?php else: ?>
      <p><a class="forum-btn primary" href="<?= htmlspecialchars(cityzen_login_url(cityzen_asset('forum/post.php?id=' . $postId))) ?>">Connectez-vous pour répondre</a></p>
    <?php endif; ?>
  </main>
</div>
<?php cityzen_render_footer(); ?>
