<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title ?? 'Administration - CityZen Forum'; ?></title>
  <link rel="stylesheet" href="/public/css/admin.css">
</head>
<body>
  <div class="layout">
    <aside class="sidebar">
      <div>
        <div class="brand">MediConnect Pro</div>
        <p>Administration</p>
      </div>
      <div>
        <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" href="/admin">Dashboard</a>
        <a class="nav-link <?php echo $currentPage === 'posts' ? 'active' : ''; ?>" href="/admin/posts">Posts</a>
        <a class="nav-link <?php echo $currentPage === 'replies' ? 'active' : ''; ?>" href="/admin/replies">Replies</a>
        <a class="nav-link" href="/">Voir le site</a>
      </div>
      <div>
        <button class="button-primary" style="width:100%;">Nouveau Dossier</button>
        <p style="margin-top: 18px; color: #94a3b8;">Aide</p>
        <p style="color: #94a3b8;">Déconnexion</p>
      </div>
    </aside>

    <main class="content">
      <?php echo $content; ?>
    </main>
  </div>

  <script src="/public/js/admin.js"></script>
</body>
</html>