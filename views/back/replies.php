<?php
$title = 'Gestion des Replies';
$currentPage = 'replies';
ob_start();
?>

<div class="topbar">
  <div class="title-group">
    <h1>Replies</h1>
    <p>Gestion de toutes les réponses du forum.</p>
  </div>
  <a class="button-primary" href="/admin/replies/create">Nouvelle Reply</a>
</div>

<section class="section">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Post associé</th>
          <th>Répondeur</th>
          <th>Date</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($replies as $reply): ?>
          <tr>
            <td><?php echo htmlspecialchars($reply['post_title']); ?></td>
            <td><?php echo htmlspecialchars($reply['author']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($reply['created_at'])); ?></td>
            <td><span class="badge badge-<?php
              echo $reply['status'] === 'approved' ? 'success' :
                   ($reply['status'] === 'rejected' ? 'danger' : 'warning');
            ?>">
              <?php
              echo $reply['status'] === 'approved' ? 'Approuvé' :
                   ($reply['status'] === 'rejected' ? 'Rejeté' : 'En attente');
              ?>
            </span></td>
            <td>
              <?php if ($reply['status'] === 'pending'): ?>
                <a href="/admin/replies/<?php echo $reply['id']; ?>/approve" class="button-approve">Approuver</a>
                <a href="/admin/replies/<?php echo $reply['id']; ?>/reject" class="button-reject">Rejeter</a>
              <?php endif; ?>
              <a href="/admin/replies/<?php echo $reply['id']; ?>/edit" class="button-edit">Modifier</a>
              <a href="/admin/replies/<?php echo $reply['id']; ?>/delete" class="button-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette reply ?')">Supprimer</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/admin.php';
?>