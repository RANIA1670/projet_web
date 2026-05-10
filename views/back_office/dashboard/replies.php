<?php
/**
 * Dashboard Replies - Gestion des réponses
 */

$allPosts = Post::findAll();
$allReplies = [];
$controller = new ForumController();

// Récupérer toutes les réponses
foreach ($allPosts as $post) {
    $replies = Reply::findByPostId($post->getId());
    foreach ($replies as $reply) {
        $allReplies[] = [
            'reply' => $reply,
            'post_id' => $post->getId(),
            'post_title' => $post->getTitle()
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_delete_reply'])) {
    header('Content-Type: application/json; charset=utf-8');
    $replyId = (int)($_POST['reply_id'] ?? 0);
    if ($replyId > 0) {
        $controller->deleteReply($replyId);
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}
?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Auteur</th>
                <th>Post</th>
                <th>Contenu</th>
                <th>Date de Création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($allReplies) {
                foreach ($allReplies as $item) {
                    $reply = $item['reply'];
                    ?>
                    <tr id="reply-row-<?= (int)$reply->getId() ?>">
                        <td>#<?php echo $reply->getId(); ?></td>
                        <td><span class="badge blue">User #<?php echo $reply->getUserId(); ?></span></td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                            <a href="#" style="color: var(--accent); text-decoration: none;">
                                <?php echo htmlspecialchars(substr($item['post_title'], 0, 50)); ?>
                            </a>
                        </td>
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; font-size: 13px; color: var(--muted);">
                            <?php echo htmlspecialchars(substr($reply->getContent(), 0, 60)); ?>...
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($reply->getCreatedAt())); ?></td>
                        <td>
                            <a href="index.php?page=edit_reply&id=<?= (int)$reply->getId() ?>&post_id=<?= (int)$item['post_id'] ?>" class="action-btn" style="display:inline-block;text-decoration:none;text-align:center;">✏️ Éditer</a>
                            <button type="button" class="action-btn danger" style="cursor:pointer;border:none;font:inherit;" onclick="deleteReply(<?= (int)$reply->getId() ?>)">🗑️ Supprimer</button>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="6" style="text-align: center; color: var(--muted);">Aucune réponse trouvée</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
<script>
function deleteReply(replyId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette réponse ?')) {
        return;
    }
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax_delete_reply=1&reply_id=' + encodeURIComponent(replyId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('reply-row-' + replyId);
            if (row) {
                row.style.opacity = '0.5';
                setTimeout(() => row.remove(), 300);
            }
        } else {
            alert('Erreur lors de la suppression.');
        }
    })
    .catch(() => {
        alert('Erreur réseau.');
    });
}
</script>
