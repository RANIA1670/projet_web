<?php
$title = 'Dashboard - Administration';
$currentPage = 'dashboard';
ob_start();
?>

<div class="topbar">
  <div class="title-group">
    <h1>Gestion du Forum</h1>
    <p>Tableau de bord de gestion des posts et des réponses.</p>
  </div>
  <a class="button-primary" href="/admin/posts/create">Nouveau Post</a>
</div>

<div class="cards">
  <div class="card">
    <span style="background: #e0f2fe; color: #1d4ed8;">TOTAL POSTS</span>
    <h2><?php echo $stats['total']; ?></h2>
    <p>Nombre total de publications dans la section forum.</p>
  </div>
  <div class="card">
    <span style="background: #ddf7e7; color: #15803d;">NOUVELLES</span>
    <h2><?php echo $stats['recent']; ?></h2>
    <p>Posts créés cette semaine par les utilisateurs.</p>
  </div>
  <div class="card">
    <span style="background: #fee2e2; color: #b91c1c;">EN ATTENTE</span>
    <h2><?php echo count($pendingReplies); ?></h2>
    <p>Réponses nécessitant validation par un modérateur.</p>
  </div>
</div>

<div class="dashboard-grid">
  <section class="section">
    <div class="section-header">
      <h2>Posts récents</h2>
      <a href="/admin/posts" class="button-secondary">Voir tout</a>
    </div>
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
          <?php foreach (array_slice($posts, 0, 5) as $post): ?>
            <tr>
              <td><?php echo htmlspecialchars($post['title']); ?></td>
              <td><?php echo htmlspecialchars($post['author']); ?></td>
              <td><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></td>
              <td><span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                <?php echo $post['status'] === 'published' ? 'Publié' : 'Brouillon'; ?>
              </span></td>
              <td>
                <a href="/admin/posts/<?php echo $post['id']; ?>/edit" class="button-edit">Modifier</a>
                <a href="/admin/posts/<?php echo $post['id']; ?>/delete" class="button-delete" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2>Réponses en attente</h2>
      <a href="/admin/replies" class="button-secondary">Voir tout</a>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Post associé</th>
            <th>Répondeur</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($pendingReplies, 0, 5) as $reply): ?>
            <tr>
              <td><?php echo htmlspecialchars($reply['post_title']); ?></td>
              <td><?php echo htmlspecialchars($reply['author']); ?></td>
              <td><?php echo date('d/m/Y', strtotime($reply['created_at'])); ?></td>
              <td>
                <a href="/admin/replies/<?php echo $reply['id']; ?>/approve" class="button-approve">Approuver</a>
                <a href="/admin/replies/<?php echo $reply['id']; ?>/reject" class="button-reject">Rejeter</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<div class="note">
  <div>
    <strong>Note :</strong>
    Respecte le modèle MVC et utilise PDO pour la gestion de la base de données.
  </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/admin.php';
?>