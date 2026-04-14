<?php
// Vue: Dashboard d'administration
$pageTitle = 'Dashboard';
ob_start();
?>

<div class="dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3><?php echo $stats['total_posts']; ?></h3>
                <p>Total Posts</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🆕</div>
            <div class="stat-content">
                <h3><?php echo $stats['recent_posts']; ?></h3>
                <p>Nouveaux (7 jours)</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <h3><?php echo $stats['pending_posts']; ?></h3>
                <p>Posts en attente</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-content">
                <h3><?php echo $stats['total_replies']; ?></h3>
                <p>Total Replies</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🔄</div>
            <div class="stat-content">
                <h3><?php echo $stats['pending_replies']; ?></h3>
                <p>Replies en attente</p>
            </div>
        </div>
    </div>

    <div class="quick-actions">
        <h2>Actions rapides</h2>
        <div class="actions-grid">
            <a href="/admin.php?action=post_create" class="action-card">
                <div class="action-icon">➕</div>
                <div class="action-content">
                    <h3>Nouveau Post</h3>
                    <p>Créer un nouveau post</p>
                </div>
            </a>

            <a href="/admin.php?action=reply_create" class="action-card">
                <div class="action-icon">💬</div>
                <div class="action-content">
                    <h3>Nouvelle Reply</h3>
                    <p>Ajouter une réponse</p>
                </div>
            </a>

            <a href="/admin.php?action=posts" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-content">
                    <h3>Gérer Posts</h3>
                    <p>Modifier ou supprimer</p>
                </div>
            </a>

            <a href="/admin.php?action=replies" class="action-card">
                <div class="action-icon">🔧</div>
                <div class="action-content">
                    <h3>Gérer Replies</h3>
                    <p>Valider ou rejeter</p>
                </div>
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/admin_layout.php';
?>