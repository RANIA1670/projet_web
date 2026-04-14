<?php
$title = 'Gestion des Posts';
$currentPage = 'posts';
ob_start();
?>

<div class="topbar">
  <div class="title-group">
    <h1>Posts</h1>
    <p>Gestion de tous les posts du forum.</p>
  </div>
  <a class="button-primary" href="/admin/posts/create">Nouveau Post</a>
</div>

<section class="section">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Titre</th>
          <th>Auteur</th>
          <th>Date</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($posts as $post): ?>
          <tr>
            <td><?php echo htmlspecialchars($post['title']); ?></td>
            <td><?php echo htmlspecialchars($post['author']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></td>
            <td><span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
              <?php echo $post['status'] === 'published' ? 'Publié' : 'Brouillon'; ?>
            </span></td>
            <td>
              <a href="/admin/posts/<?php echo $post['id']; ?>/edit" class="button-edit">Modifier</a>
              <a href="/admin/posts/<?php echo $post['id']; ?>/delete" class="button-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')">Supprimer</a>
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