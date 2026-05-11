<?php
/**
 * Dashboard Posts - Gestion des posts avec actions en masse
 */
require_once __DIR__ . '/../../../config/ForumRedirect.php';
require_once __DIR__ . '/../../../models/Report.php';
$adminEntry = forum_admin_nav_base();
$controller = new ForumController();

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($searchQuery !== '') {
    $allPosts = Post::search($searchQuery);
} else {
    $allPosts = Post::findAll();
}

// Bulk action handler
$bulkMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'], $_POST['post_ids'])) {
    $action  = $_POST['bulk_action'];
    $ids     = array_map('intval', (array)$_POST['post_ids']);
    $ids     = array_filter($ids, fn($id) => $id > 0);
    $ok = 0;
    foreach ($ids as $id) {
        if ($action === 'bulk_delete') {
            if ($controller->deletePost($id)) $ok++;
        } elseif ($action === 'bulk_feature') {
            if (Post::setFeatured($id, true)) $ok++;
        }
    }
    $label = $action === 'bulk_delete' ? 'supprimé(s)' : 'mis en avant';
    $bulkMessage = "<div style='background:rgba(46,204,113,.12);border:1px solid rgba(46,204,113,.3);color:#1f8a4a;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:.875rem;'>✅ {$ok} post(s) {$label}.</div>";
    // Refresh list
    $allPosts = $searchQuery !== '' ? Post::search($searchQuery) : Post::findAll();
}

// AJAX single delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_delete_post'])) {
    header('Content-Type: application/json; charset=utf-8');
    $postId = (int)($_POST['post_id'] ?? 0);
    echo json_encode(['success' => $postId > 0 && $controller->deletePost($postId)]);
    exit;
}
?>

<style>
    .bulk-bar {
        display: none;
        align-items: center;
        gap: 12px;
        background: rgba(52,73,94,.07);
        border: 1px solid rgba(52,73,94,.18);
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 14px;
        font-size: .875rem;
        font-weight: 600;
        color: var(--text);
    }
    .bulk-bar.visible { display: flex; }
    .bulk-select-count { flex: 1; }
    .btn-bulk {
        padding: 7px 16px;
        border: none;
        border-radius: 6px;
        font-size: .8rem;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: opacity .2s;
    }
    .btn-bulk:hover { opacity: .85; }
    .btn-bulk-delete  { background: rgba(231,76,60,.14); color: #c0392b; }
    .btn-bulk-feature { background: rgba(243,156,18,.14); color: #b7770d; }
    .cb-post { accent-color: var(--accent); width: 15px; height: 15px; cursor: pointer; }
</style>

<?php if ($bulkMessage) echo $bulkMessage; ?>

<!-- Search bar -->
<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px 20px;margin-bottom:16px;display:flex;gap:12px;align-items:center;">
    <form method="GET" action="<?= htmlspecialchars($adminEntry, ENT_QUOTES, 'UTF-8') ?>" style="display:flex;gap:10px;width:100%;align-items:center;">
        <input type="hidden" name="page" value="dashboard">
        <input type="text" name="q" placeholder="Chercher un post par titre…"
               value="<?= htmlspecialchars($searchQuery) ?>"
               style="flex:1;padding:9px 14px;border:1px solid var(--border);border-radius:6px;font-size:.875rem;outline:none;">
        <button type="submit" class="action-btn" style="background:var(--accent);color:#fff;border:none;cursor:pointer;">🔍 Chercher</button>
        <?php if ($searchQuery !== ''): ?>
            <a href="<?= htmlspecialchars($adminEntry, ENT_QUOTES, 'UTF-8') ?>?page=dashboard" class="action-btn" style="text-decoration:none;text-align:center;">✕ Effacer</a>
        <?php endif; ?>
    </form>
</div>

<!-- Bulk action bar -->
<form method="POST" action="<?= htmlspecialchars($adminEntry, ENT_QUOTES, 'UTF-8') ?>?page=dashboard" id="bulkForm">
<div class="bulk-bar" id="bulkBar">
    <span class="bulk-select-count"><span id="selectedCount">0</span> sélectionné(s)</span>
    <select name="bulk_action" id="bulkActionSelect" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:.8rem;font-family:inherit;">
        <option value="">-- Action --</option>
        <option value="bulk_delete">🗑️ Supprimer</option>
        <option value="bulk_feature">📌 Mettre en avant</option>
    </select>
    <button type="submit" class="btn-bulk btn-bulk-delete" id="bulkSubmit"
            onclick="return confirmBulk()">Appliquer</button>
    <button type="button" class="btn-bulk" style="background:#eee;color:#666;"
            onclick="deselectAll()">Annuler</button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width:36px;"><input type="checkbox" id="selectAll" class="cb-post" title="Tout sélectionner"></th>
                <th>ID</th>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Réponses</th>
                <th>Vues</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($allPosts): foreach ($allPosts as $post):
                $replyCount = Reply::countByPostId($post->getId());
                $isFeatured = method_exists($post, 'getIsFeatured') && (int)$post->getIsFeatured() === 1;
            ?>
            <tr id="post-row-<?= $post->getId() ?>">
                <td><input type="checkbox" name="post_ids[]" value="<?= $post->getId() ?>" class="cb-post post-cb"></td>
                <td style="color:var(--text-secondary);font-size:.8rem;">#<?= $post->getId() ?></td>
                <td style="font-weight:500;max-width:200px;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars($post->getTitle()) ?>
                    <?php if ($isFeatured): ?><span style="font-size:.7rem;background:rgba(243,156,18,.15);color:#b7770d;padding:2px 7px;border-radius:20px;margin-left:6px;">📌 En avant</span><?php endif; ?>
                </td>
                <td><span class="badge blue">User #<?= $post->getUserId() ?></span></td>
                <td><span class="badge orange"><?= $replyCount ?></span></td>
                <td><?= $post->getViewCount() ?></td>
                <td style="font-size:.8rem;color:var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($post->getCreatedAt())) ?></td>
                <td>
                    <a href="<?= htmlspecialchars($adminEntry) ?>?page=edit_post&id=<?= $post->getId() ?>"
                       class="action-btn" style="display:inline-block;text-decoration:none;text-align:center;">✏️ Éditer</a>
                    <button type="button" class="action-btn danger" style="cursor:pointer;border:none;font:inherit;"
                            onclick="deletePost(<?= $post->getId() ?>)">🗑️ Supprimer</button>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="8" style="text-align:center;color:var(--muted);">Aucun post trouvé</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</form>

<script>
/* ── Checkboxes & bulk ── */
var selectAll = document.getElementById('selectAll');
var bulkBar   = document.getElementById('bulkBar');
var countEl   = document.getElementById('selectedCount');
var cbs       = document.querySelectorAll('.post-cb');

function updateBulkBar() {
    var checked = document.querySelectorAll('.post-cb:checked').length;
    countEl.textContent = checked;
    bulkBar.classList.toggle('visible', checked > 0);
}

selectAll.addEventListener('change', function() {
    cbs.forEach(function(cb) { cb.checked = selectAll.checked; });
    updateBulkBar();
});

cbs.forEach(function(cb) {
    cb.addEventListener('change', function() {
        var allChecked = document.querySelectorAll('.post-cb:checked').length === cbs.length;
        selectAll.checked = allChecked;
        updateBulkBar();
    });
});

function deselectAll() {
    cbs.forEach(function(cb) { cb.checked = false; });
    selectAll.checked = false;
    updateBulkBar();
}

function confirmBulk() {
    var action = document.getElementById('bulkActionSelect').value;
    var count  = document.querySelectorAll('.post-cb:checked').length;
    if (!action) { alert('Choisissez une action.'); return false; }
    if (count === 0) { alert('Aucun post sélectionné.'); return false; }
    var labels = { bulk_delete: 'supprimer', bulk_feature: 'mettre en avant' };
    return confirm('Voulez-vous ' + (labels[action] || action) + ' ' + count + ' post(s) ?');
}

/* ── Single delete AJAX ── */
function deletePost(postId) {
    if (!confirm('Supprimer ce post ?')) return;
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ajax_delete_post=1&post_id=' + postId
    }).then(function(r) { return r.json(); }).then(function(d) {
        if (d.success) {
            var row = document.getElementById('post-row-' + postId);
            if (row) { row.style.opacity = '0.4'; setTimeout(function() { row.remove(); }, 300); }
        } else { alert('Erreur lors de la suppression.'); }
    }).catch(function() { alert('Erreur réseau.'); });
}
</script>
