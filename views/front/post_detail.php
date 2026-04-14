<?php
// Vue: Détail d'un post avec ses replies (front office)
$pageTitle = htmlspecialchars($post['title']);
ob_start();
?>

<div class="post-detail">
    <article class="post-full">
        <header class="post-header">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <span class="author">Par <?php echo htmlspecialchars($post['author']); ?></span>
                <span class="date">Le <?php echo date('d/m/Y à H:i', strtotime($post['created_at'])); ?></span>
                <span class="views">👁 <?php echo $post['views']; ?> vues</span>
            </div>
        </header>

        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
    </article>

    <section class="replies-section">
        <div class="replies-header">
            <h2>Réponses (<?php echo count($replies); ?>)</h2>
        </div>

        <?php if (empty($replies)): ?>
            <div class="no-replies">
                <p>Soyez le premier à répondre à ce post !</p>
            </div>
        <?php else: ?>
            <div class="replies-list">
                <?php foreach ($replies as $reply): ?>
                    <?php if ($reply['status'] === 'Validé'): ?>
                        <div class="reply">
                            <div class="reply-header">
                                <span class="author"><?php echo htmlspecialchars($reply['author']); ?></span>
                                <span class="date"><?php echo date('d/m/Y à H:i', strtotime($reply['created_at'])); ?></span>
                            </div>
                            <div class="reply-content">
                                <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="reply-form-container">
            <h3>Répondre à ce post</h3>
            <form action="/index.php?action=reply_store" method="POST" class="reply-form">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

                <div class="form-group">
                    <label for="author">Votre nom *</label>
                    <input type="text" id="author" name="author" required>
                </div>

                <div class="form-group">
                    <label for="author_email">Email (optionnel)</label>
                    <input type="email" id="author_email" name="author_email">
                </div>

                <div class="form-group">
                    <label for="content">Votre réponse *</label>
                    <textarea id="content" name="content" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Envoyer la réponse</button>
            </form>
        </div>
    </section>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>