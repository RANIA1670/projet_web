<?php
$title = htmlspecialchars($post['title']);
ob_start();
?>

<div class="container">
  <article class="post-detail">
    <header class="post-header">
      <h1><?php echo htmlspecialchars($post['title']); ?></h1>
      <p class="post-meta">
        Par <?php echo htmlspecialchars($post['author']); ?> le
        <?php echo date('d/m/Y à H:i', strtotime($post['created_at'])); ?>
      </p>
    </header>

    <div class="post-content">
      <?php echo nl2br(htmlspecialchars($post['content'])); ?>
    </div>
  </article>

  <section class="replies-section">
    <h2>Réponses (<?php echo count($replies); ?>)</h2>

    <?php if (empty($replies)): ?>
      <p class="no-replies">Aucune réponse pour le moment. Soyez le premier à répondre !</p>
    <?php else: ?>
      <div class="replies-list">
        <?php foreach ($replies as $reply): ?>
          <div class="reply">
            <div class="reply-header">
              <strong><?php echo htmlspecialchars($reply['author']); ?></strong>
              <span class="reply-date"><?php echo date('d/m/Y à H:i', strtotime($reply['created_at'])); ?></span>
            </div>
            <div class="reply-content">
              <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="reply-form-container">
      <h3>Ajouter une réponse</h3>
      <form action="/reply" method="POST" class="reply-form">
        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

        <div class="form-group">
          <label for="author">Votre nom</label>
          <input type="text" id="author" name="author" required>
        </div>

        <div class="form-group">
          <label for="content">Votre réponse</label>
          <textarea id="content" name="content" rows="5" required></textarea>
        </div>

        <button type="submit" class="button-primary">Publier la réponse</button>
      </form>
    </div>
  </section>

  <div class="back-link">
    <a href="/">← Retour au forum</a>
  </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>