<?php
$title = isset($post) ? 'Modifier le Post' : 'Nouveau Post';
$currentPage = 'posts';
ob_start();
?>

<div class="topbar">
  <div class="title-group">
    <h1><?php echo isset($post) ? 'Modifier le Post' : 'Nouveau Post'; ?></h1>
    <p><?php echo isset($post) ? 'Modifiez les informations du post.' : 'Créez un nouveau post pour le forum.'; ?></p>
  </div>
</div>

<section class="section">
  <form action="<?php echo isset($post) ? '/admin/posts/' . $post['id'] . '/edit' : '/admin/posts/create'; ?>" method="POST" class="form">
    <div class="form-group">
      <label for="title">Titre</label>
      <input type="text" id="title" name="title" value="<?php echo isset($post) ? htmlspecialchars($post['title']) : ''; ?>" required>
    </div>

    <div class="form-group">
      <label for="author">Auteur</label>
      <input type="text" id="author" name="author" value="<?php echo isset($post) ? htmlspecialchars($post['author']) : ''; ?>" required>
    </div>

    <div class="form-group">
      <label for="content">Contenu</label>
      <textarea id="content" name="content" rows="10" required><?php echo isset($post) ? htmlspecialchars($post['content']) : ''; ?></textarea>
    </div>

    <div class="form-group">
      <label for="status">Statut</label>
      <select id="status" name="status" required>
        <option value="draft" <?php echo (isset($post) && $post['status'] === 'draft') ? 'selected' : ''; ?>>Brouillon</option>
        <option value="published" <?php echo (isset($post) && $post['status'] === 'published') ? 'selected' : ''; ?>>Publié</option>
      </select>
    </div>

    <div class="form-actions">
      <a href="/admin/posts" class="button-secondary">Annuler</a>
      <button type="submit" class="button-primary"><?php echo isset($post) ? 'Modifier' : 'Créer'; ?></button>
    </div>
  </form>
</section>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/admin.php';
?>