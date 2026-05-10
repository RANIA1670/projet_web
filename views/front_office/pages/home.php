<?php
/**
 * Page d'accueil - Liste des posts (app.php)
 */
$allPosts = Post::findAll();
$showCreated = isset($_GET['created']);
?>

<?php if ($showCreated): ?>
    <div class="flash-success" role="status">✅ Votre discussion a été publiée.</div>
<?php endif; ?>

<h1 style="font-size: 28px; font-weight: 700; margin-bottom: 32px;">💬 Tous les discussions</h1>

<?php if ($allPosts): ?>
    <div class="posts-list">
        <?php foreach ($allPosts as $post):
            $replies = Reply::findByPostId($post->getId());
            $replyCount = count($replies);
        ?>
            <a class="post-card" href="<?= htmlspecialchars(forum_post_url($post->getId())) ?>" style="text-decoration:none;color:inherit;display:block;">
                <div class="post-header">
                    <div>
                        <div class="post-title"><?php echo htmlspecialchars($post->getTitle()); ?></div>
                        <div class="post-meta">
                            <span>👤 Utilisateur #<?php echo $post->getUserId(); ?></span>
                            <span>📅 <?php echo date('d M Y', strtotime($post->getCreatedAt())); ?></span>
                        </div>
                    </div>
                </div>

                <div class="post-content">
                    <?php echo htmlspecialchars(substr($post->getContent(), 0, 150)); ?>...
                </div>

                <div class="post-footer">
                    <div class="post-stats">
                        <span>💬 <?php echo $replyCount; ?> réponses</span>
                        <span>👀 <?php echo $post->getViewCount(); ?> vues</span>
                    </div>
                    <span class="icon-btn" style="pointer-events:none;">Lire →</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">📭</div>
        <h3>Aucun post pour le moment</h3>
        <p>Soyez le premier à créer une discussion !</p>
        <button class="btn-primary" onclick="openCreateModal()" style="margin-top: 16px;">➕ Créer un post</button>
    </div>
<?php endif; ?>
