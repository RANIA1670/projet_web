<?php

declare(strict_types=1);

cityzen_render_head('Tableau de bord');
?>
<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <span>City<strong>Zen</strong></span>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($cityzen['admin_menu'] as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= $item['key'] === 'dashboard' ? 'is-active' : '' ?>">
          <span class="nav-bullet"></span>
          <?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
      <a href="<?= htmlspecialchars(cityzen_asset('controller/statistics.php')) ?>" class="nav-statistics">
        <span class="nav-bullet"></span>
        📊 Statistiques
      </a>
    </nav>
  </aside>

  <main class="admin-page">
    <header class="admin-header">
      <div>
        <h1>Tableau de bord - <?= htmlspecialchars($cityzen['city_name']) ?></h1>
        <p class="admin-header-lead">Le tableau de bord s&apos;appuie sur les comptes reels de la base pour suivre les utilisateurs, les admins, les citoyens simples et les comptes bloques.</p>
      </div>
      <div class="admin-user">
        <span><?= htmlspecialchars($cityzen['current_date']) ?></span>
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

    <section class="panel users-admin-section" id="utilisateurs">
      <h2>Comptes utilisateurs</h2>
      <p class="panel-lead">Gestion complete dans l&apos;espace dedie : liste, recherche, tri, pagination, modification, suppression et export PDF.</p>
      <p><a class="users-admin-cta" href="<?= htmlspecialchars(cityzen_asset('controller/users.php')) ?>">Ouvrir la gestion des utilisateurs</a></p>
    </section>

    <section class="panel statistics-summary-section" id="statistiques">
      <h2>📊 Statistiques détaillées</h2>
      <p class="panel-lead">Consultez des statistiques complètes avec graphiques, tendances et analyses détaillées de l&apos;activité des utilisateurs.</p>
      <div class="stats-preview">
        <div class="stat-preview-item">
          <strong><?= $userStats['total'] ?></strong>
          <span>Total utilisateurs</span>
        </div>
        <div class="stat-preview-item">
          <strong><?= $userStats['admins'] ?></strong>
          <span>Administrateurs</span>
        </div>
        <div class="stat-preview-item">
          <strong><?= $userStats['users'] ?></strong>
          <span>Utilisateurs actifs</span>
        </div>
        <div class="stat-preview-item">
          <strong><?= $userStats['blocked'] ?></strong>
          <span>Comptes bloqués</span>
        </div>
      </div>
      <p><a class="users-admin-cta" href="<?= htmlspecialchars(cityzen_asset('controller/statistics.php')) ?>">📊 Voir les statistiques complètes</a></p>
    </section>

    <section class="admin-stats">
      <article class="admin-stat-card interactive-card" data-card>
        <strong class="accent-green" data-count-to="<?= $userStats['total'] ?>"><?= $userStats['total'] ?></strong>
        <span>Utilisateurs</span>
        <small>Total des comptes en base</small>
      </article>
      <article class="admin-stat-card interactive-card" data-card>
        <strong data-count-to="<?= $userStats['admins'] ?>"><?= $userStats['admins'] ?></strong>
        <span>Administrateurs</span>
        <small>Comptes avec acces admin</small>
      </article>
      <article class="admin-stat-card interactive-card" data-card>
        <strong class="accent-orange" data-count-to="<?= $userStats['users'] ?>"><?= $userStats['users'] ?></strong>
        <span>Utilisateurs simples</span>
        <small>Comptes citoyens</small>
      </article>
      <article class="admin-stat-card interactive-card" data-card>
        <strong class="accent-red" data-count-to="<?= $userStats['blocked'] ?>"><?= $userStats['blocked'] ?></strong>
        <span>Utilisateurs bloques</span>
        <small>Acces desactive a la connexion</small>
      </article>
    </section>

    <section class="admin-grid">
      <article class="panel" id="projets">
        <h2>Participation par quartier</h2>
        <div class="district-list">
          <?php foreach ($cityzen['districts'] as $district): ?>
            <div class="district-row">
              <span><?= htmlspecialchars($district['name']) ?></span>
              <div class="progress-track">
                <div class="progress-fill <?= htmlspecialchars($district['tone']) ?>" data-width="<?= (int) $district['value'] ?>" style="width: 0"></div>
              </div>
              <strong><?= htmlspecialchars((string) $district['value']) ?>%</strong>
            </div>
          <?php endforeach; ?>
        </div>
      </article>

      <article class="panel" id="signalements">
        <h2>Signalements cette semaine</h2>
        <div class="week-chart">
          <?php foreach ($cityzen['weekly_reports'] as $bar): ?>
            <div class="bar-item">
              <div class="bar <?= htmlspecialchars($bar['tone']) ?>" data-height="<?= max(18, (int) $bar['value']) ?>" style="height: 10px"></div>
              <span><?= htmlspecialchars($bar['day']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="summary-cards">
          <div class="summary-card success">
            <strong><?= htmlspecialchars((string) $cityzen['weekly_summary']['resolved']) ?></strong>
            <span>Resolus</span>
          </div>
          <div class="summary-card danger">
            <strong><?= htmlspecialchars((string) $cityzen['weekly_summary']['pending']) ?></strong>
            <span>En attente</span>
          </div>
        </div>
      </article>
    </section>

    <section class="panel reports-table-section" id="citoyens">
      <h2>Derniers signalements - A traiter</h2>
      <p class="panel-lead">Aligne sur la liste publique ; ici en mode file de traitement.</p>
      <div class="reports-table">
        <div class="table-head">
          <span>Incident</span>
          <span>Quartier</span>
          <span>Categorie</span>
          <span>Date</span>
          <span>Statut</span>
        </div>
        <?php foreach ($cityzen['recent_reports'] as $report): ?>
          <div class="table-row interactive-row" data-row>
            <span><?= htmlspecialchars($report['title']) ?></span>
            <span><?= htmlspecialchars($report['district']) ?></span>
            <span><?= htmlspecialchars($report['category']) ?></span>
            <span><?= htmlspecialchars($report['date']) ?></span>
            <span><em class="pill <?= htmlspecialchars($report['severity']) ?>"><?= htmlspecialchars($report['status']) ?></em></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>
