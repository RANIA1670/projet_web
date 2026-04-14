<?php
// Vue: Gestion des posts (administration)
$pageTitle = 'Gestion des Posts';
ob_start();
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Gestion des Posts</h2>
        <a href="/admin.php?action=post_create" class="btn btn-primary">Nouveau Post</a>
    </div>

    <div class="stats-summary">
        <div class="stat-item">
            <span class="stat-label">Total:</span>
            <span class="stat-value"><?php echo $stats['total']; ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Nouveaux (7j):</span>
            <span class="stat-value"><?php echo $stats['recent']; ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">En attente:</span>
            <span class="stat-value"><?php echo $stats['pending']; ?></span>
        </div>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Vues</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo $post['id']; ?></td>
                        <td><?php echo htmlspecialchars(substr($post['title'], 0, 50)); ?><?php echo strlen($post['title']) > 50 ? '...' : ''; ?></td>
                        <td><?php echo htmlspecialchars($post['author']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($post['status']); ?>">
                                <?php echo $post['status']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></td>
                        <td><?php echo $post['views']; ?></td>
                        <td class="actions">
                            <a href="/admin.php?action=post_edit&id=<?php echo $post['id']; ?>" class="btn btn-small btn-edit">Modifier</a>
                            <a href="/admin.php?action=post_delete&id=<?php echo $post['id']; ?>" class="btn btn-small btn-delete" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/admin_layout.php';
?>