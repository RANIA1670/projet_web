<?php
// Vue: Liste des posts (front office)
$pageTitle = 'Forum CityZen';
ob_start();
?>

<div class="posts-container">
    <div class="posts-header">
        <h2>Posts du Forum</h2>
        <a href="/index.php?action=create" class="btn btn-primary">Nouveau Post</a>
    </div>

    <?php if (empty($posts)): ?>
        <div class="no-posts">
            <p>Aucun post publié pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <div class="post-header">
                        <h3><a href="/index.php?action=show&id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                        <div class="post-meta">
                            <span class="author">Par <?php echo htmlspecialchars($post['author']); ?></span>
                            <span class="date"><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></span>
                        </div>
                    </div>

                    <?php if (!empty($post['content'])): ?>
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>
                            <?php if (strlen($post['content']) > 200): ?>...<?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="post-footer">
                        <div class="post-stats">
                            <span>👁 <?php echo $post['views']; ?> vues</span>
                            <span>👍 <?php echo $post['likes']; ?> likes</span>
                        </div>
                        <a href="/index.php?action=show&id=<?php echo $post['id']; ?>" class="btn btn-secondary">Voir les réponses</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>