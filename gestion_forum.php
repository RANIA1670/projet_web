<?php

require_once __DIR__ . '/includes/layout.php';

cityzen_render_head('Gestion Forum');
?>
<style>
  .forum-shell {
    width: min(96%, 1120px);
    margin: 0 auto;
  }

  .forum-inner {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 20px;
    margin-top: 20px;
  }

  .forum-sidebar {
    background: var(--nav);
    color: rgba(255, 255, 255, 0.92);
    border-radius: 18px;
    overflow: hidden;
  }

  .forum-sidebar .sidebar-brand {
    padding: 22px 18px;
    font-size: 1.65rem;
  }

  .forum-sidebar .sidebar-nav a {
    color: rgba(255, 255, 255, 0.85);
    padding: 14px 18px;
    transition: background 0.2s ease;
  }

  .forum-sidebar .sidebar-nav a.is-active,
  .forum-sidebar .sidebar-nav a:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
  }

  .forum-page {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 24px;
    box-shadow: var(--shadow);
  }

  .forum-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 22px;
  }

  .forum-header h1 {
    margin: 0;
    font-size: clamp(2rem, 3vw, 2.8rem);
  }

  .forum-header p {
    margin: 10px 0 0;
    color: var(--muted);
  }

  .forum-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }

  .forum-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 20px;
    box-shadow: var(--shadow);
  }

  .forum-card h3 {
    margin: 0 0 8px;
    font-size: 0.8rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--green);
  }

  .forum-card strong {
    display: block;
    margin-top: 12px;
    font-size: 2.4rem;
    line-height: 1;
  }

  .forum-actions {
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }

  .button-primary,
  .button-secondary {
    border: 0;
    border-radius: 999px;
    font-weight: 800;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease;
  }

  .button-primary {
    padding: 12px 20px;
    background: var(--green);
    color: #fff;
  }

  .button-primary:hover {
    transform: translateY(-1px);
  }

  .button-secondary {
    padding: 12px 20px;
    background: #f9fafb;
    color: var(--ink);
    border: 1px solid var(--line);
  }

  .button-secondary:hover {
    background: #eef2f6;
  }

  .badge-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0.9rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .badge-success { background: #edf7ed; color: #2d8a3c; }
  .badge-warning { background: #fff4e5; color: #b55808; }
  .badge-danger { background: #fdecee; color: #9e2a20; }

  .forum-table {
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid rgba(217, 223, 231, 0.9);
  }

  .table-head,
  .table-row {
    display: grid;
    grid-template-columns: 2fr 1fr 0.9fr 0.8fr 1fr;
    gap: 16px;
    align-items: center;
    padding: 14px 18px;
  }

  .table-head {
    background: var(--surface-soft);
    color: var(--muted);
    font-size: 0.85rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .table-row {
    background: #fff;
    border-top: 1px solid rgba(217, 223, 231, 0.9);
  }

  .table-row span {
    color: var(--ink);
  }

  .table-row .actions {
    display: flex;
    gap: 8px;
  }

  .forum-note {
    margin-top: 22px;
    padding: 18px 20px;
    border-radius: 16px;
    border: 1px solid #cfe5f7;
    background: #f2f8ff;
    color: #164e7b;
  }

  @media (max-width: 980px) {
    .forum-inner { grid-template-columns: 1fr; }
    .forum-summary { grid-template-columns: 1fr; }
    .table-head,
    .table-row { grid-template-columns: 1.6fr 1fr 0.9fr 0.9fr 1fr; }
  }
</style>
<div class="site-shell">
  <header class="topbar topbar-public">
    <div class="brand">
      <span class="brand-dot"></span>
      <span class="brand-text">City <strong>Zen</strong></span>
    </div>
    <nav class="main-nav">
      <?php foreach ($cityzen['public_menu'] as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= $item['key'] === 'gestion-forum' ? 'is-active' : '' ?>">
          <?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="topbar-actions">
      <button class="avatar avatar-warning" type="button">O</button>
      <button class="avatar avatar-success" type="button"><?= htmlspecialchars($cityzen['user']['initials']) ?></button>
    </div>
  </header>

  <div class="forum-shell">
    <div class="forum-inner">
      <aside class="forum-sidebar">
        <div class="sidebar-brand">MediConnect <strong>Pro</strong></div>
        <nav class="sidebar-nav">
          <a href="#" class="is-active">Dashboard</a>
          <a href="#">Signalements</a>
          <a href="#">Projets</a>
          <a href="#">Paramètres</a>
        </nav>
      </aside>

      <main class="forum-page">
        <div class="forum-header">
          <div>
            <h1>Gestion du Forum</h1>
            <p>Tableau de bord de gestion des posts et des réponses.</p>
          </div>
          <div class="forum-actions">
            <button class="button-primary" type="button" onclick="openPostModal()">Nouveau Post</button>
          </div>
        </div>

        <div class="forum-summary">
          <article class="forum-card">
            <h3>Total posts</h3>
            <strong id="totalPosts">0</strong>
            <p>Nombre total de publications dans la section forum.</p>
          </article>
          <article class="forum-card">
            <h3>Nouvelles</h3>
            <strong id="newPosts">0</strong>
            <p>Posts créés cette semaine par les utilisateurs.</p>
          </article>
          <article class="forum-card">
            <h3>En attente</h3>
            <strong id="pendingReplies">0</strong>
            <p>Réponses nécessitant validation par un modérateur.</p>
          </article>
        </div>

        <section class="panel">
          <div class="section-header">
            <h2>Posts</h2>
            <div class="forum-actions">
              <span class="badge-pill badge-success">Actifs</span>
              <button class="button-primary" type="button" onclick="openPostModal()">Ajouter Post</button>
            </div>
          </div>
          <div class="forum-table" id="postsTable">
            <div class="table-head">
              <span>Titre</span>
              <span>Auteur</span>
              <span>Date</span>
              <span>Statut</span>
              <span>Actions</span>
            </div>
          </div>
        </section>

        <section class="panel" style="margin-top: 22px;">
          <div class="section-header">
            <h2>Replies</h2>
            <div class="forum-actions">
              <span class="badge-pill badge-warning">À vérifier</span>
              <button class="button-primary" type="button" onclick="openReplyModal()">Ajouter Reply</button>
            </div>
          </div>
          <div class="forum-table" id="repliesTable">
            <div class="table-head">
              <span>Post associé</span>
              <span>Répondeur</span>
              <span>Date</span>
              <span>Statut</span>
              <span>Actions</span>
            </div>
          </div>
          <div class="forum-note">
            <strong>Note :</strong> Respecte le modèle MVC et utilise PDO pour la gestion de la base de données.
          </div>
        </section>
      </main>
    </div>
  </div>
</div>

<div id="postModal" class="modal" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="postModalTitle">Ajouter un Post</h3>
      <span class="close" onclick="closePostModal()">&times;</span>
    </div>
    <form id="postForm">
      <input type="hidden" id="postId" value="">
      <div class="form-group">
        <label for="postTitle">Titre</label>
        <input type="text" id="postTitle" required>
      </div>
      <div class="form-group">
        <label for="postAuthor">Auteur</label>
        <input type="text" id="postAuthor" required>
      </div>
      <div class="form-group">
        <label for="postStatus">Statut</label>
        <select id="postStatus" required>
          <option value="Publié">Publié</option>
          <option value="En révision">En révision</option>
          <option value="Brouillon">Brouillon</option>
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="button-secondary" onclick="closePostModal()">Annuler</button>
        <button type="submit" class="button-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<div id="replyModal" class="modal" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="replyModalTitle">Ajouter une Reply</h3>
      <span class="close" onclick="closeReplyModal()">&times;</span>
    </div>
    <form id="replyForm">
      <input type="hidden" id="replyId" value="">
      <div class="form-group">
        <label for="replyPost">Post associé</label>
        <select id="replyPost" required></select>
      </div>
      <div class="form-group">
        <label for="replyAuthor">Répondeur</label>
        <input type="text" id="replyAuthor" required>
      </div>
      <div class="form-group">
        <label for="replyStatus">Statut</label>
        <select id="replyStatus" required>
          <option value="Validé">Validé</option>
          <option value="Rejeté">Rejeté</option>
          <option value="En attente">En attente</option>
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="button-secondary" onclick="closeReplyModal()">Annuler</button>
        <button type="submit" class="button-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
  let posts = [
    { id: 1, title: 'Présentation de la nouvelle fonctionnalité', author: 'Amine B.', date: '12 avr. 2026', status: 'Publié' },
    { id: 2, title: 'Question sur l\'intégration PDO', author: 'Lea M.', date: '11 avr. 2026', status: 'En révision' },
    { id: 3, title: 'Problème avec les formulaires', author: 'Sofia R.', date: '10 avr. 2026', status: 'Publié' }
  ];

  let replies = [
    { id: 1, postTitle: 'Question sur l\'intégration PDO', author: 'Marc L.', date: '12 avr. 2026', status: 'Validé' },
    { id: 2, postTitle: 'Problème avec les formulaires', author: 'Yasmine F.', date: '11 avr. 2026', status: 'Rejeté' },
    { id: 3, postTitle: 'Présentation de la nouvelle fonctionnalité', author: 'Oumeima I.', date: '10 avr. 2026', status: 'En attente' }
  ];

  function loadData() {
    const savedPosts = localStorage.getItem('posts');
    const savedReplies = localStorage.getItem('replies');
    if (savedPosts) posts = JSON.parse(savedPosts);
    if (savedReplies) replies = JSON.parse(savedReplies);
  }

  function saveData() {
    localStorage.setItem('posts', JSON.stringify(posts));
    localStorage.setItem('replies', JSON.stringify(replies));
  }

  function renderPosts() {
    const container = document.getElementById('postsTable');
    container.querySelectorAll('.table-row').forEach(node => node.remove());

    posts.forEach(post => {
      const row = document.createElement('div');
      row.className = 'table-row';
      row.innerHTML = `
        <span>${post.title}</span>
        <span>${post.author}</span>
        <span>${post.date}</span>
        <span><span class="badge-pill ${getStatusClass(post.status)}">${post.status}</span></span>
        <span class="actions">
          <button class="button-secondary" type="button" onclick="editPost(${post.id})">Modifier</button>
          <button class="button-secondary" type="button" onclick="deletePost(${post.id})">Supprimer</button>
        </span>
      `;
      container.appendChild(row);
    });
    updatePostSelect();
    updateStats();
  }

  function renderReplies() {
    const container = document.getElementById('repliesTable');
    container.querySelectorAll('.table-row').forEach(node => node.remove());

    replies.forEach(reply => {
      const row = document.createElement('div');
      row.className = 'table-row';
      row.innerHTML = `
        <span>${reply.postTitle}</span>
        <span>${reply.author}</span>
        <span>${reply.date}</span>
        <span><span class="badge-pill ${getStatusClass(reply.status)}">${reply.status}</span></span>
        <span class="actions">
          <button class="button-secondary" type="button" onclick="editReply(${reply.id})">Modifier</button>
          <button class="button-secondary" type="button" onclick="deleteReply(${reply.id})">Supprimer</button>
        </span>
      `;
      container.appendChild(row);
    });
    updateStats();
  }

  function getStatusClass(status) {
    switch (status) {
      case 'Publié':
      case 'Validé':
        return 'badge-success';
      case 'En révision':
      case 'En attente':
        return 'badge-warning';
      case 'Rejeté':
        return 'badge-danger';
      default:
        return 'badge-success';
    }
  }

  function updateStats() {
    document.getElementById('totalPosts').textContent = posts.length;
    document.getElementById('newPosts').textContent = posts.filter(p => new Date(p.date) > new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)).length;
    document.getElementById('pendingReplies').textContent = replies.filter(r => r.status === 'En attente').length;
  }

  function updatePostSelect() {
    const select = document.getElementById('replyPost');
    select.innerHTML = '';
    posts.forEach(post => {
      const option = document.createElement('option');
      option.value = post.title;
      option.textContent = post.title;
      select.appendChild(option);
    });
  }

  function openPostModal(postId = null) {
    const modal = document.getElementById('postModal');
    const title = document.getElementById('postModalTitle');
    const form = document.getElementById('postForm');

    if (postId) {
      const post = posts.find(p => p.id === postId);
      document.getElementById('postId').value = post.id;
      document.getElementById('postTitle').value = post.title;
      document.getElementById('postAuthor').value = post.author;
      document.getElementById('postStatus').value = post.status;
      title.textContent = 'Modifier un Post';
    } else {
      form.reset();
      document.getElementById('postId').value = '';
      title.textContent = 'Ajouter un Post';
    }

    modal.style.display = 'flex';
  }

  function closePostModal() {
    document.getElementById('postModal').style.display = 'none';
  }

  function openReplyModal(replyId = null) {
    const modal = document.getElementById('replyModal');
    const title = document.getElementById('replyModalTitle');
    const form = document.getElementById('replyForm');

    if (replyId) {
      const reply = replies.find(r => r.id === replyId);
      document.getElementById('replyId').value = reply.id;
      document.getElementById('replyPost').value = reply.postTitle;
      document.getElementById('replyAuthor').value = reply.author;
      document.getElementById('replyStatus').value = reply.status;
      title.textContent = 'Modifier une Reply';
    } else {
      form.reset();
      document.getElementById('replyId').value = '';
      title.textContent = 'Ajouter une Reply';
    }

    modal.style.display = 'flex';
  }

  function closeReplyModal() {
    document.getElementById('replyModal').style.display = 'none';
  }

  document.getElementById('postForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('postId').value;
    const title = document.getElementById('postTitle').value;
    const author = document.getElementById('postAuthor').value;
    const status = document.getElementById('postStatus').value;

    if (id) {
      const post = posts.find(p => p.id == id);
      post.title = title;
      post.author = author;
      post.status = status;
    } else {
      posts.push({ id: Date.now(), title, author, date: new Date().toLocaleDateString('fr-FR'), status });
    }

    saveData();
    renderPosts();
    closePostModal();
  });

  document.getElementById('replyForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('replyId').value;
    const postTitle = document.getElementById('replyPost').value;
    const author = document.getElementById('replyAuthor').value;
    const status = document.getElementById('replyStatus').value;

    if (id) {
      const reply = replies.find(r => r.id == id);
      reply.postTitle = postTitle;
      reply.author = author;
      reply.status = status;
    } else {
      replies.push({ id: Date.now(), postTitle, author, date: new Date().toLocaleDateString('fr-FR'), status });
    }

    saveData();
    renderReplies();
    closeReplyModal();
  });

  function editPost(id) { openPostModal(id); }
  function deletePost(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce post ?')) return;
    posts = posts.filter(p => p.id !== id);
    replies = replies.filter(r => r.postTitle !== posts.find(p => p.id === id)?.title);
    saveData();
    renderPosts();
    renderReplies();
  }

  function editReply(id) { openReplyModal(id); }
  function deleteReply(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette reply ?')) return;
    replies = replies.filter(r => r.id !== id);
    saveData();
    renderReplies();
  }

  window.onclick = function (event) {
    const postModal = document.getElementById('postModal');
    const replyModal = document.getElementById('replyModal');
    if (event.target === postModal) closePostModal();
    if (event.target === replyModal) closeReplyModal();
  }

  loadData();
  renderPosts();
  renderReplies();
</script>

<?php
cityzen_render_footer();
?>
