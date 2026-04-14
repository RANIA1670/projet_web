<?php
// Vue: Gestion des replies (administration)
$pageTitle = 'Gestion des Replies';
ob_start();
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Gestion des Replies</h2>
        <a href="/admin.php?action=reply_create" class="btn btn-primary">Nouvelle Reply</a>
    </div>

    <div class="stats-summary">
        <div class="stat-item">
            <span class="stat-label">Total:</span>
            <span class="stat-value"><?php echo $stats['total']; ?></span>
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
                    <th>Post associé</th>
                    <th>Auteur</th>
                    <th>Contenu</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($replies as $reply): ?>
                    <tr>
                        <td><?php echo $reply['id']; ?></td>
                        <td><?php echo htmlspecialchars(substr($reply['post_title'], 0, 30)); ?><?php echo strlen($reply['post_title']) > 30 ? '...' : ''; ?></td>
                        <td><?php echo htmlspecialchars($reply['author']); ?></td>
                        <td><?php echo htmlspecialchars(substr($reply['content'], 0, 50)); ?><?php echo strlen($reply['content']) > 50 ? '...' : ''; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($reply['status']); ?>">
                                <?php echo $reply['status']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($reply['created_at'])); ?></td>
                        <td class="actions">
                            <a href="/admin.php?action=reply_edit&id=<?php echo $reply['id']; ?>" class="btn btn-small btn-edit">Modifier</a>
                            <a href="/admin.php?action=reply_delete&id=<?php echo $reply['id']; ?>" class="btn btn-small btn-delete" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
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