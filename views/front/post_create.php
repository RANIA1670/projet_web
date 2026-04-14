<?php
// Vue: Formulaire de création de post (front office)
$pageTitle = 'Nouveau Post';
ob_start();
?>

<div class="create-post">
    <div class="form-container">
        <h1>Créer un nouveau post</h1>

        <form action="/index.php?action=store" method="POST" class="post-form">
            <div class="form-group">
                <label for="title">Titre du post *</label>
                <input type="text" id="title" name="title" required maxlength="255">
            </div>

            <div class="form-group">
                <label for="author">Votre nom *</label>
                <input type="text" id="author" name="author" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="author_email">Email (optionnel)</label>
                <input type="email" id="author_email" name="author_email" maxlength="255">
            </div>

            <div class="form-group">
                <label for="content">Contenu du post *</label>
                <textarea id="content" name="content" rows="10" required></textarea>
            </div>

            <div class="form-actions">
                <a href="/index.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Publier le post</button>
            </div>
        </form>

        <div class="info-box">
            <p><strong>Note :</strong> Votre post sera soumis à validation avant d'être publié sur le forum.</p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>