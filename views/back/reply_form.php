<?php
$title = isset($reply) ? 'Modifier la Reply' : 'Nouvelle Reply';
$currentPage = 'replies';
ob_start();
?>

<div class="topbar">
  <div class="title-group">
    <h1><?php echo isset($reply) ? 'Modifier la Reply' : 'Nouvelle Reply'; ?></h1>
    <p><?php echo isset($reply) ? 'Modifiez les informations de la reply.' : 'Créez une nouvelle reply pour le forum.'; ?></p>
  </div>
</div>

<section class="section">
  <form action="<?php echo isset($reply) ? '/admin/replies/' . $reply['id'] . '/edit' : '/admin/replies/create'; ?>" method="POST" class="form">
    <div class="form-group">
      <label for="post_id">Post associé</label>
      <select id="post_id" name="post_id" required>
        <option value="">Sélectionnez un post</option>
        <?php foreach ($posts as $post): ?>
          <option value="<?php echo $post['id']; ?>" <?php echo (isset($reply) && $reply['post_id'] == $post['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($post['title']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="author">Répondeur</label>
      <input type="text" id="author" name="author" value="<?php echo isset($reply) ? htmlspecialchars($reply['author']) : ''; ?>" required>
    </div>

    <div class="form-group">
      <label for="content">Contenu</label>
      <textarea id="content" name="content" rows="8" required><?php echo isset($reply) ? htmlspecialchars($reply['content']) : ''; ?></textarea>
    </div>

    <div class="form-group">
      <label for="status">Statut</label>
      <select id="status" name="status" required>
        <option value="pending" <?php echo (isset($reply) && $reply['status'] === 'pending') ? 'selected' : ''; ?>>En attente</option>
        <option value="approved" <?php echo (isset($reply) && $reply['status'] === 'approved') ? 'selected' : ''; ?>>Approuvé</option>
        <option value="rejected" <?php echo (isset($reply) && $reply['status'] === 'rejected') ? 'selected' : ''; ?>>Rejeté</option>
      </select>
    </div>

    <div class="form-actions">
      <a href="/admin/replies" class="button-secondary">Annuler</a>
      <button type="submit" class="button-primary"><?php echo isset($reply) ? 'Modifier' : 'Créer'; ?></button>
    </div>
  </form>
</section>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/admin.php';
?>