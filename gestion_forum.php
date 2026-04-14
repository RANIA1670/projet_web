<?php

require_once __DIR__ . '/includes/layout.php';

cityzen_render_head('Gestion Forum');
?>
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

  <main class="page public-page">
    <!-- Forum content here -->
    <div class="layout" style="margin-top: 20px;">
      <aside class="sidebar">
        <div>
          <div class="brand">MediConnect Pro</div>
          <p>Administration</p>
        </div>
        <div>
          <a class="nav-link active" href="#">Dashboard</a>
          <a class="nav-link" href="#">Signalements</a>
          <a class="nav-link" href="#">Projets</a>
          <a class="nav-link" href="#">Paramètres</a>
        </div>
        <div>
          <button class="button-primary" style="width:100%;">Nouveau Dossier</button>
          <p style="margin-top: 18px; color: #94a3b8;">Aide</p>
          <p style="color: #94a3b8;">Déconnexion</p>
        </div>
      </aside>

      <main class="content" style="padding: 28px 32px;">
        <div class="topbar" style="margin-bottom: 30px; gap: 18px; justify-content: space-between;">
          <div class="title-group">
            <h1>Gestion du Forum</h1>
            <p>Tableau de bord de gestion des posts et des réponses.</p>
          </div>
          <a class="button-primary" href="#">Nouveau Post</a>
        </div>

        <div class="cards" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; margin-bottom: 28px;">
          <div class="card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 22px; box-shadow: 0 18px 50px rgba(15, 23, 42, 0.06);">
            <span style="background: #e0f2fe; color: #1d4ed8;">TOTAL POSTS</span>
            <h2>312</h2>
            <p>Nombre total de publications dans la section forum.</p>
          </div>
          <div class="card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 22px; box-shadow: 0 18px 50px rgba(15, 23, 42, 0.06);">
            <span style="background: #ddf7e7; color: #15803d;">NOUVELLES</span>
            <h2>48</h2>
            <p>Posts créés cette semaine par les utilisateurs.</p>
          </div>
          <div class="card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 22px; box-shadow: 0 18px 50px rgba(15, 23, 42, 0.06);">
            <span style="background: #fee2e2; color: #b91c1c;">EN ATTENTE</span>
            <h2>9</h2>
            <p>Réponses nécessitant validation par un modérateur.</p>
          </div>
        </div>

        <section class="section" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 28px; padding: 28px;">
          <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; gap: 18px; margin-bottom: 22px;">
            <h2>Posts</h2>
            <div style="display: flex; gap: 12px;">
              <span class="badge" style="background: #dcfce7; color: #15803d;">Actifs</span>
              <button class="button-primary" onclick="openPostModal()">Ajouter Post</button>
            </div>
          </div>
          <div class="table-wrapper" style="overflow-x: auto;">
            <table id="postsTable" style="width: 100%; border-collapse: collapse; min-width: 720px;">
              <thead style="background: #f8fafc;">
                <tr>
                  <th style="text-align: left; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Titre</th>
                  <th style="text-align: left; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Auteur</th>
                  <th style="text-align: left; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Date</th>
                  <th style="text-align: left; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Statut</th>
                  <th style="text-align: left; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Les données seront chargées dynamiquement -->
              </tbody>
            </table>
          </div>
        </section>

        <section class="section" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 28px; padding: 28px; margin-top: 24px;">
          <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; gap: 18px; margin-bottom: 22px;">
            <h2>Replies</h2>
            <div style="display: flex; gap: 12px;">
              <span class="badge" style="background: #ffedd5; color: #b45309;">À vérifier</span>
              <button class="button-primary" onclick="openReplyModal()">Ajouter Reply</button>
            </div>
          </div>
          <div class="table-wrapper" style="overflow-x: auto;">
            <table id="repliesTable" style="width: 100%; border-collapse: collapse; min-width: 720px;">
              <thead style="background: #f8fafc;">
                <tr>
                  <th style="text-align: left; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Post associé</th>
                  <th style="text-align: left; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Répondeur</th>
                  <th style="text-align: left; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Date</th>
                  <th style="text-align: left; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Statut</th>
                  <th style="text-align: left; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; font-size: 0.95rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Les données seront chargées dynamiquement -->
              </tbody>
            </table>
          </div>
          <div class="note" style="display: flex; align-items: center; gap: 16px; padding: 18px 22px; margin-top: 26px; border-radius: 20px; background: #f0f9ff; border: 1px solid #bae6fd; color: #0c4a6e;">
            <div>
              <strong>Note :</strong>
              Respecte le modèle MVC et utilise PDO pour la gestion de la base de données.
            </div>
          </div>
        </section>

        <!-- Modal pour les Posts -->
        <div id="postModal" class="modal" style="position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; display: none;">
          <div class="modal-content" style="background: #ffffff; border-radius: 24px; padding: 0; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 24px 28px; border-bottom: 1px solid #e2e8f0;">
              <h3 id="postModalTitle">Ajouter un Post</h3>
              <span class="close" style="font-size: 28px; font-weight: bold; color: #64748b; cursor: pointer; line-height: 1;" onclick="closePostModal()">&times;</span>
            </div>
            <form id="postForm">
              <input type="hidden" id="postId" value="">
              <div class="form-group" style="padding: 20px 28px; border-bottom: 1px solid #f1f5f9;">
                <label for="postTitle" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0f172a;">Titre</label>
                <input type="text" id="postTitle" style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.96rem; background: #ffffff;" required>
              </div>
              <div class="form-group" style="padding: 20px 28px; border-bottom: 1px solid #f1f5f9;">
                <label for="postAuthor" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0f172a;">Auteur</label>
                <input type="text" id="postAuthor" style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.96rem; background: #ffffff;" required>
              </div>
              <div class="form-group" style="padding: 20px 28px;">
                <label for="postStatus" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0f172a;">Statut</label>
                <select id="postStatus" style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.96rem; background: #ffffff;" required>
                  <option value="Publié">Publié</option>
                  <option value="En révision">En révision</option>
                  <option value="Brouillon">Brouillon</option>
                </select>
              </div>
              <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 12px; padding: 24px 28px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="button-secondary" style="display: inline-flex; align-items: center; justify-content: center; padding: 12px 20px; border-radius: 12px; background: #f1f5f9; color: #0f172a; border: 1px solid #e2e8f0; cursor: pointer;" onclick="closePostModal()">Annuler</button>
                <button type="submit" class="button-primary" style="display: inline-flex; align-items: center; justify-content: center; padding: 12px 20px; border-radius: 12px; background: #2563eb; color: #ffffff; border: none; cursor: pointer;">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Modal pour les Replies -->
        <div id="replyModal" class="modal" style="position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; display: none;">
          <div class="modal-content" style="background: #ffffff; border-radius: 24px; padding: 0; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 24px 28px; border-bottom: 1px solid #e2e8f0;">
              <h3 id="replyModalTitle">Ajouter une Reply</h3>
              <span class="close" style="font-size: 28px; font-weight: bold; color: #64748b; cursor: pointer; line-height: 1;" onclick="closeReplyModal()">&times;</span>
            </div>
            <form id="replyForm">
              <input type="hidden" id="replyId" value="">
              <div class="form-group" style="padding: 20px 28px; border-bottom: 1px solid #f1f5f9;">
                <label for="replyPost" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0f172a;">Post associé</label>
                <select id="replyPost" style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.96rem; background: #ffffff;" required>
                  <!-- Les options seront remplies dynamiquement -->
                </select>
              </div>
              <div class="form-group" style="padding: 20px 28px; border-bottom: 1px solid #f1f5f9;">
                <label for="replyAuthor" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0f172a;">Répondeur</label>
                <input type="text" id="replyAuthor" style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.96rem; background: #ffffff;" required>
              </div>
              <div class="form-group" style="padding: 20px 28px;">
                <label for="replyStatus" style="display: block; margin-bottom: 8px; font-weight: 600; color: #0f172a;">Statut</label>
                <select id="replyStatus" style="width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.96rem; background: #ffffff;" required>
                  <option value="Validé">Validé</option>
                  <option value="Rejeté">Rejeté</option>
                  <option value="En attente">En attente</option>
                </select>
              </div>
              <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 12px; padding: 24px 28px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="button-secondary" style="display: inline-flex; align-items: center; justify-content: center; padding: 12px 20px; border-radius: 12px; background: #f1f5f9; color: #0f172a; border: 1px solid #e2e8f0; cursor: pointer;" onclick="closeReplyModal()">Annuler</button>
                <button type="submit" class="button-primary" style="display: inline-flex; align-items: center; justify-content: center; padding: 12px 20px; border-radius: 12px; background: #2563eb; color: #ffffff; border: none; cursor: pointer;">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </main>
</div>

<script>
  // Données initiales
  let posts = [
    { id: 1, title: "Présentation de la nouvelle fonctionnalité", author: "Amine B.", date: "12 avr. 2026", status: "Publié" },
    { id: 2, title: "Question sur l'intégration PDO", author: "Lea M.", date: "11 avr. 2026", status: "En révision" },
    { id: 3, title: "Problème avec les formulaires", author: "Sofia R.", date: "10 avr. 2026", status: "Publié" }
  ];

  let replies = [
    { id: 1, postTitle: "Question sur l'intégration PDO", author: "Marc L.", date: "12 avr. 2026", status: "Validé" },
    { id: 2, postTitle: "Problème avec les formulaires", author: "Yasmine F.", date: "11 avr. 2026", status: "Rejeté" },
    { id: 3, postTitle: "Présentation de la nouvelle fonctionnalité", author: "Oumeima I.", date: "10 avr. 2026", status: "En attente" }
  ];

  // Charger les données depuis localStorage
  function loadData() {
    const savedPosts = localStorage.getItem('posts');
    const savedReplies = localStorage.getItem('replies');
    if (savedPosts) posts = JSON.parse(savedPosts);
    if (savedReplies) replies = JSON.parse(savedReplies);
  }

  // Sauvegarder les données dans localStorage
  function saveData() {
    localStorage.setItem('posts', JSON.stringify(posts));
    localStorage.setItem('replies', JSON.stringify(replies));
  }

  // Afficher les posts
  function renderPosts() {
    const tbody = document.querySelector('#postsTable tbody');
    tbody.innerHTML = '';
    posts.forEach(post => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${post.title}</td>
        <td>${post.author}</td>
        <td>${post.date}</td>
        <td><span class="badge" style="background: ${getStatusColor(post.status)}; color: ${getStatusTextColor(post.status)};">${post.status}</span></td>
        <td>
          <button class="button-edit" style="padding: 6px 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600; margin-right: 8px; background: #e0f2fe; color: #1d4ed8;" onclick="editPost(${post.id})">Modifier</button>
          <button class="button-delete" style="padding: 6px 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600; margin-right: 8px; background: #fee2e2; color: #b91c1c;" onclick="deletePost(${post.id})">Supprimer</button>
        </td>
      `;
      tbody.appendChild(row);
    });
    updatePostSelect();
    updateStats();
  }

  // Afficher les replies
  function renderReplies() {
    const tbody = document.querySelector('#repliesTable tbody');
    tbody.innerHTML = '';
    replies.forEach(reply => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${reply.postTitle}</td>
        <td>${reply.author}</td>
        <td>${reply.date}</td>
        <td><span class="badge" style="background: ${getStatusColor(reply.status)}; color: ${getStatusTextColor(reply.status)};">${reply.status}</span></td>
        <td>
          <button class="button-edit" style="padding: 6px 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600; margin-right: 8px; background: #e0f2fe; color: #1d4ed8;" onclick="editReply(${reply.id})">Modifier</button>
          <button class="button-delete" style="padding: 6px 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600; margin-right: 8px; background: #fee2e2; color: #b91c1c;" onclick="deleteReply(${reply.id})">Supprimer</button>
        </td>
      `;
      tbody.appendChild(row);
    });
    updateStats();
  }

  // Obtenir la couleur pour le statut
  function getStatusColor(status) {
    switch(status) {
      case 'Publié':
      case 'Validé': return '#dcfce7';
      case 'En révision':
      case 'En attente': return '#ffedd5';
      case 'Rejeté': return '#fee2e2';
      default: return '#dcfce7';
    }
  }

  function getStatusTextColor(status) {
    switch(status) {
      case 'Publié':
      case 'Validé': return '#15803d';
      case 'En révision':
      case 'En attente': return '#b45309';
      case 'Rejeté': return '#b91c1c';
      default: return '#15803d';
    }
  }

  // Mettre à jour les statistiques
  function updateStats() {
    const totalPosts = posts.length;
    const newPosts = posts.filter(p => new Date(p.date) > new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)).length;
    const pendingReplies = replies.filter(r => r.status === 'En attente').length;

    document.querySelector('.cards .card:nth-child(1) h2').textContent = totalPosts;
    document.querySelector('.cards .card:nth-child(2) h2').textContent = newPosts;
    document.querySelector('.cards .card:nth-child(3) h2').textContent = pendingReplies;
  }

  // Mettre à jour le select des posts pour les replies
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

  // Ouvrir la modal des posts
  function openPostModal(postId = null) {
    const modal = document.getElementById('postModal');
    const form = document.getElementById('postForm');
    const title = document.getElementById('postModalTitle');

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

  // Fermer la modal des posts
  function closePostModal() {
    document.getElementById('postModal').style.display = 'none';
  }

  // Ouvrir la modal des replies
  function openReplyModal(replyId = null) {
    const modal = document.getElementById('replyModal');
    const form = document.getElementById('replyForm');
    const title = document.getElementById('replyModalTitle');

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

  // Fermer la modal des replies
  function closeReplyModal() {
    document.getElementById('replyModal').style.display = 'none';
  }

  // Soumettre le formulaire des posts
  document.getElementById('postForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('postId').value;
    const title = document.getElementById('postTitle').value;
    const author = document.getElementById('postAuthor').value;
    const status = document.getElementById('postStatus').value;

    if (id) {
      // Modifier
      const post = posts.find(p => p.id == id);
      post.title = title;
      post.author = author;
      post.status = status;
    } else {
      // Ajouter
      const newPost = {
        id: Date.now(),
        title,
        author,
        date: new Date().toLocaleDateString('fr-FR'),
        status
      };
      posts.push(newPost);
    }

    saveData();
    renderPosts();
    closePostModal();
  });

  // Soumettre le formulaire des replies
  document.getElementById('replyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('replyId').value;
    const postTitle = document.getElementById('replyPost').value;
    const author = document.getElementById('replyAuthor').value;
    const status = document.getElementById('replyStatus').value;

    if (id) {
      // Modifier
      const reply = replies.find(r => r.id == id);
      reply.postTitle = postTitle;
      reply.author = author;
      reply.status = status;
    } else {
      // Ajouter
      const newReply = {
        id: Date.now(),
        postTitle,
        author,
        date: new Date().toLocaleDateString('fr-FR'),
        status
      };
      replies.push(newReply);
    }

    saveData();
    renderReplies();
    closeReplyModal();
  });

  // Modifier un post
  function editPost(id) {
    openPostModal(id);
  }

  // Supprimer un post
  function deletePost(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce post ?')) {
      posts = posts.filter(p => p.id !== id);
      // Supprimer aussi les replies associées
      replies = replies.filter(r => r.postTitle !== posts.find(p => p.id === id)?.title);
      saveData();
      renderPosts();
      renderReplies();
    }
  }

  // Modifier une reply
  function editReply(id) {
    openReplyModal(id);
  }

  // Supprimer une reply
  function deleteReply(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette reply ?')) {
      replies = replies.filter(r => r.id !== id);
      saveData();
      renderReplies();
    }
  }

  // Fermer les modales en cliquant en dehors
  window.onclick = function(event) {
    const postModal = document.getElementById('postModal');
    const replyModal = document.getElementById('replyModal');
    if (event.target === postModal) {
      closePostModal();
    }
    if (event.target === replyModal) {
      closeReplyModal();
    }
  }

  // Initialisation
  loadData();
  renderPosts();
  renderReplies();
</script>

<?php
cityzen_render_footer();
?>