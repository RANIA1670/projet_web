<?php
$title = 'Forum CityZen';
ob_start();
?>

<div class="hero">
  <div class="container">
    <h1>Bienvenue sur le Forum CityZen</h1>
    <p>Échangez et partagez vos connaissances sur le développement web</p>
    <a href="#posts" class="button-primary">Voir les posts</a>
  </div>
</div>

<section id="posts" class="posts-section">
  <div class="container">
    <h2>Derniers Posts</h2>
    <div class="posts-grid">
      <?php foreach ($posts as $post): ?>
        <article class="post-card">
          <h3><a href="/post/<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
          <p class="post-meta">
            Par <?php echo htmlspecialchars($post['author']); ?> le
            <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
          </p>
          <p class="post-excerpt">
            <?php echo htmlspecialchars(substr($post['content'], 0, 150)) . '...'; ?>
          </p>
          <a href="/post/<?php echo $post['id']; ?>" class="read-more">Lire la suite</a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout/main.php';
?>