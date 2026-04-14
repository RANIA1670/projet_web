<?php
// Vue: Formulaire de reply (administration)
$pageTitle = isset($reply) ? 'Modifier la Reply' : 'Nouvelle Reply';
ob_start();
?>

<div class="admin-section">
    <div class="form-container">
        <h2><?php echo isset($reply) ? 'Modifier la Reply' : 'Nouvelle Reply'; ?></h2>

        <form action="/admin.php?action=<?php echo isset($reply) ? 'reply_update&id=' . $reply['id'] : 'reply_store'; ?>" method="POST" class="admin-form">
            <div class="form-group">
                <label for="post_id">Post associé *</label>
                <select id="post_id" name="post_id" required>
                    <option value="">Sélectionner un post</option>
                    <?php foreach ($posts as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo (isset($reply) && $reply['post_id'] == $p['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="author">Auteur *</label>
                <input type="text" id="author" name="author" value="<?php echo isset($reply) ? htmlspecialchars($reply['author']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="author_email">Email auteur</label>
                <input type="email" id="author_email" name="author_email" value="<?php echo isset($reply) ? htmlspecialchars($reply['author_email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="status">Statut *</label>
                <select id="status" name="status" required>
                    <option value="Validé" <?php echo (isset($reply) && $reply['status'] === 'Validé') ? 'selected' : ''; ?>>Validé</option>
                    <option value="Rejeté" <?php echo (isset($reply) && $reply['status'] === 'Rejeté') ? 'selected' : ''; ?>>Rejeté</option>
                    <option value="En attente" <?php echo (isset($reply) && $reply['status'] === 'En attente') ? 'selected' : ''; ?>>En attente</option>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Contenu *</label>
                <textarea id="content" name="content" rows="8" required><?php echo isset($reply) ? htmlspecialchars($reply['content']) : ''; ?></textarea>
            </div>

            <div class="form-actions">
                <a href="/admin.php?action=replies" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><?php echo isset($reply) ? 'Mettre à jour' : 'Créer'; ?></button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/admin_layout.php';
?>