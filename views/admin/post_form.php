<?php
// Vue: Formulaire de post (administration)
$pageTitle = isset($post) ? 'Modifier le Post' : 'Nouveau Post';
ob_start();
?>

<div class="admin-section">
    <div class="form-container">
        <h2><?php echo isset($post) ? 'Modifier le Post' : 'Nouveau Post'; ?></h2>

        <form action="/admin.php?action=<?php echo isset($post) ? 'post_update&id=' . $post['id'] : 'post_store'; ?>" method="POST" class="admin-form">
            <div class="form-group">
                <label for="title">Titre *</label>
                <input type="text" id="title" name="title" value="<?php echo isset($post) ? htmlspecialchars($post['title']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="author">Auteur *</label>
                <input type="text" id="author" name="author" value="<?php echo isset($post) ? htmlspecialchars($post['author']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="author_email">Email auteur</label>
                <input type="email" id="author_email" name="author_email" value="<?php echo isset($post) ? htmlspecialchars($post['author_email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="status">Statut *</label>
                <select id="status" name="status" required>
                    <option value="Publié" <?php echo (isset($post) && $post['status'] === 'Publié') ? 'selected' : ''; ?>>Publié</option>
                    <option value="En révision" <?php echo (isset($post) && $post['status'] === 'En révision') ? 'selected' : ''; ?>>En révision</option>
                    <option value="Brouillon" <?php echo (isset($post) && $post['status'] === 'Brouillon') ? 'selected' : ''; ?>>Brouillon</option>
                    <option value="Archivé" <?php echo (isset($post) && $post['status'] === 'Archivé') ? 'selected' : ''; ?>>Archivé</option>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Contenu</label>
                <textarea id="content" name="content" rows="10"><?php echo isset($post) ? htmlspecialchars($post['content']) : ''; ?></textarea>
            </div>

            <div class="form-actions">
                <a href="/admin.php?action=posts" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><?php echo isset($post) ? 'Mettre à jour' : 'Créer'; ?></button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/admin_layout.php';
?>