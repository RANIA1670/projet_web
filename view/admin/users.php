<?php

declare(strict_types=1);

use App\Helpers\UsersPageHelper;

cityzen_render_head('Gestion des utilisateurs');
?>
<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <span>City<strong>Zen</strong></span>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($cityzen['admin_menu'] as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= $item['key'] === 'users' ? 'is-active' : '' ?>">
          <span class="nav-bullet"></span>
          <?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <main class="admin-page">
    <header class="admin-header">
      <div>
        <h1>Gestion des utilisateurs</h1>
        <p class="admin-header-lead">Liste, recherche, tri, modification, suppression et export PDF (via impression).</p>
      </div>
      <div class="admin-user">
        <a class="btn-ghost" href="<?= htmlspecialchars(cityzen_asset('controller/dashboard.php')) ?>">Tableau de bord</a>
        <?php $avatarUrl = cityzen_user_avatar_url(); ?>
        <a href="<?= htmlspecialchars(cityzen_asset('controller/settings.php')) ?>" aria-label="Ouvrir les parametres">
          <?php if ($avatarUrl !== null): ?>
            <img class="avatar avatar-link avatar-photo" src="<?= htmlspecialchars($avatarUrl) ?>" alt="Photo de profil">
          <?php else: ?>
            <span class="avatar avatar-success avatar-link"><?= htmlspecialchars(cityzen_user_initials()) ?></span>
          <?php endif; ?>
        </a>
      </div>
    </header>

    <?php if (is_array($flash) && isset($flash['msg'])): ?>
      <p class="admin-flash <?= ($flash['type'] ?? '') === 'success' ? 'admin-flash-success' : 'admin-flash-error' ?>" role="status"><?= htmlspecialchars((string) $flash['msg']) ?></p>
    <?php endif; ?>

    <?php if ($editUser !== null): ?>
      <section class="panel users-edit-panel">
        <h2>Modifier l&apos;utilisateur</h2>
        <p class="panel-lead"><strong><?= htmlspecialchars($editUser['username']) ?></strong> &mdash; l&apos;admin peut changer le role et bloquer/debloquer le compte, mais pas modifier son identifiant ni son mot de passe ici.</p>
        <form class="users-edit-form" method="post" action="<?= htmlspecialchars($baseUrl . '?' . UsersPageHelper::buildQuery($get, ['edit' => (string) $editId])) ?>">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
          <input type="hidden" name="save_user" value="1">
          <input type="hidden" name="user_id" value="<?= (int) $editUser['id'] ?>">
          <div class="users-static-field">
            <span>Nom d&apos;utilisateur</span>
            <strong><?= htmlspecialchars($editUser['username']) ?></strong>
          </div>
          <label class="users-edit-field">
            <span>Role</span>
            <select name="role">
              <option value="user" <?= $editUser['role'] === 'user' ? 'selected' : '' ?>>Citoyen</option>
              <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
            </select>
          </label>
          <label class="users-edit-field">
            <span>Etat du compte</span>
            <select name="blocked">
              <option value="0" <?= (int) $editUser['blocked'] === 0 ? 'selected' : '' ?>>Actif</option>
              <option value="1" <?= (int) $editUser['blocked'] === 1 ? 'selected' : '' ?>>Bloque</option>
            </select>
          </label>
          <div class="users-edit-actions">
            <button type="submit" class="users-role-submit">Enregistrer</button>
            <a class="btn-ghost" href="<?= htmlspecialchars(UsersPageHelper::url($baseUrl, $get, ['edit' => ''])) ?>">Annuler</a>
          </div>
        </form>
      </section>
    <?php elseif ($editId > 0): ?>
      <p class="admin-flash admin-flash-error" role="alert">Utilisateur introuvable.</p>
    <?php endif; ?>

    <section class="panel users-mgmt-panel">
      <div class="users-toolbar">
        <form class="users-search-form" method="get" action="">
          <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
          <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
          <input type="hidden" name="per_page" value="<?= (int) $list['per_page'] ?>">
          <label class="users-search-label">
            <span class="visually-hidden">Recherche</span>
            <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Rechercher par identifiant, nom ou email...">
          </label>
          <button type="submit" class="users-search-btn">Rechercher</button>
        </form>
        <form class="users-per-page-form" method="get" action="">
          <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
          <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
          <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
          <label>
            <span class="users-per-page-label">Par page</span>
            <select name="per_page" onchange="this.form.submit()">
              <?php foreach ([5, 10, 25, 50, 100] as $pp): ?>
                <option value="<?= $pp ?>" <?= $list['per_page'] === $pp ? 'selected' : '' ?>><?= $pp ?></option>
              <?php endforeach; ?>
            </select>
          </label>
        </form>
        <a class="users-export-pdf" href="<?= htmlspecialchars($pdfHref) ?>" target="_blank" rel="noopener">Export PDF</a>
      </div>

      <p class="users-total-meta"><?= (int) $list['total'] ?> utilisateur(s) &mdash; page <?= (int) $list['page'] ?> / <?= $totalPages ?> &mdash; les comptes bloques ne peuvent plus se connecter.</p>

      <div class="reports-table users-mgmt-table">
        <div class="table-head users-mgmt-head">
          <span>
            <a class="sort-link" href="<?= htmlspecialchars(UsersPageHelper::url($baseUrl, $get, ['sort' => 'username', 'dir' => UsersPageHelper::nextDir('username', $sort, $dir), 'page' => '1'])) ?>">Utilisateur<?= $sort === 'username' ? ($dir === 'ASC' ? ' ^' : ' v') : '' ?></a>
          </span>
          <span>
            <a class="sort-link" href="<?= htmlspecialchars(UsersPageHelper::url($baseUrl, $get, ['sort' => 'full_name', 'dir' => UsersPageHelper::nextDir('full_name', $sort, $dir), 'page' => '1'])) ?>">Nom<?= $sort === 'full_name' ? ($dir === 'ASC' ? ' ^' : ' v') : '' ?></a>
          </span>
          <span>
            <a class="sort-link" href="<?= htmlspecialchars(UsersPageHelper::url($baseUrl, $get, ['sort' => 'role', 'dir' => UsersPageHelper::nextDir('role', $sort, $dir), 'page' => '1'])) ?>">Role<?= $sort === 'role' ? ($dir === 'ASC' ? ' ^' : ' v') : '' ?></a>
          </span>
          <span>
            <a class="sort-link" href="<?= htmlspecialchars(UsersPageHelper::url($baseUrl, $get, ['sort' => 'created_at', 'dir' => UsersPageHelper::nextDir('created_at', $sort, $dir), 'page' => '1'])) ?>">Inscription<?= $sort === 'created_at' ? ($dir === 'ASC' ? ' ^' : ' v') : '' ?></a>
          </span>
          <span>Etat</span>
          <span>Actions</span>
        </div>
        <?php foreach ($list['rows'] as $urow): ?>
          <?php
            $uid = (int) $urow['id'];
            $uname = (string) $urow['username'];
            $fullName = trim((string) ($urow['full_name'] ?? ''));
            $urole = (string) $urow['role'];
            $udate = (string) $urow['created_at'];
            $ublocked = (int) ($urow['blocked'] ?? 0) === 1;
          ?>
          <div class="table-row users-mgmt-row">
            <span><strong><?= htmlspecialchars($uname) ?></strong></span>
            <span><?= htmlspecialchars($fullName !== '' ? $fullName : '—') ?></span>
            <span><em class="pill <?= $urole === 'admin' ? 'progress' : 'done' ?>"><?= $urole === 'admin' ? 'Administrateur' : 'Citoyen' ?></em></span>
            <span class="users-date"><?= htmlspecialchars($udate) ?></span>
            <span><em class="pill <?= $ublocked ? 'urgent' : 'done' ?>"><?= $ublocked ? 'Bloque' : 'Actif' ?></em></span>
            <span class="users-actions-cell">
              <a class="btn-inline" href="<?= htmlspecialchars(UsersPageHelper::url($baseUrl, $get, ['edit' => (string) $uid])) ?>">Modifier</a>
              <form class="users-delete-form" method="post" action="<?= htmlspecialchars($baseUrl . '?' . UsersPageHelper::buildQuery($get, [])) ?>" onsubmit="return confirm('Supprimer definitivement cet utilisateur ?');">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
                <input type="hidden" name="delete_user" value="1">
                <input type="hidden" name="user_id" value="<?= $uid ?>">
                <button type="submit" class="btn-danger-inline">Supprimer</button>
              </form>
            </span>
          </div>
        <?php endforeach; ?>
        <?php if ($list['rows'] === []): ?>
          <p class="users-empty">Aucun utilisateur ne correspond a cette recherche.</p>
        <?php endif; ?>
      </div>

      <?php if ($totalPages > 1): ?>
        <nav class="users-pagination" aria-label="Pagination">
          <?php if ($list['page'] > 1): ?>
            <a class="pager-link" href="<?= htmlspecialchars(UsersPageHelper::url($baseUrl, $get, ['page' => (string) ($list['page'] - 1)])) ?>">Precedent</a>
          <?php endif; ?>
          <?php
            $window = 2;
            $start = max(1, $list['page'] - $window);
            $end = min($totalPages, $list['page'] + $window);
            for ($p = $start; $p <= $end; $p++):
          ?>
            <a class="pager-link <?= $p === $list['page'] ? 'is-current' : '' ?>" href="<?= htmlspecialchars(UsersPageHelper::url($baseUrl, $get, ['page' => (string) $p])) ?>"><?= $p ?></a>
          <?php endfor; ?>
          <?php if ($list['page'] < $totalPages): ?>
            <a class="pager-link" href="<?= htmlspecialchars(UsersPageHelper::url($baseUrl, $get, ['page' => (string) ($list['page'] + 1)])) ?>">Suivant</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>
