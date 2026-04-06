<?php

require_once __DIR__ . '/../includes/layout.php';

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
    </nav>
  </aside>

  <main class="admin-page">
    <header class="admin-header">
      <div>
        <h1>Tableau de bord - <?= htmlspecialchars($cityzen['city_name']) ?></h1>
      </div>
      <div class="admin-user">
        <span><?= htmlspecialchars($cityzen['current_date']) ?></span>
        <button class="avatar avatar-warning" type="button">O</button>
        <button class="avatar avatar-success" type="button"><?= htmlspecialchars($cityzen['user']['initials']) ?></button>
      </div>
    </header>

    <section class="admin-stats">
      <article class="admin-stat-card">
        <strong class="accent-green"><?= htmlspecialchars((string) $cityzen['stats']['reports']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['reports']['label']) ?></span>
        <small><?= htmlspecialchars($cityzen['stats']['reports']['trend']) ?></small>
      </article>
      <article class="admin-stat-card">
        <strong><?= htmlspecialchars($cityzen['stats']['resolution']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['resolution']['label']) ?></span>
        <small><?= htmlspecialchars($cityzen['stats']['resolution']['trend']) ?></small>
      </article>
      <article class="admin-stat-card">
        <strong class="accent-orange"><?= htmlspecialchars((string) $cityzen['stats']['projects']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['projects']['label']) ?></span>
        <small><?= htmlspecialchars($cityzen['stats']['projects']['trend']) ?></small>
      </article>
      <article class="admin-stat-card">
        <strong class="accent-red"><?= htmlspecialchars((string) $cityzen['stats']['alerts']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['alerts']['label']) ?></span>
        <small><?= htmlspecialchars($cityzen['stats']['alerts']['trend']) ?></small>
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
                <div class="progress-fill <?= htmlspecialchars($district['tone']) ?>" style="width: <?= (int) $district['value'] ?>%"></div>
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
              <div class="bar <?= htmlspecialchars($bar['tone']) ?>" style="height: <?= max(18, (int) $bar['value']) ?>px"></div>
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
      <div class="reports-table">
        <div class="table-head">
          <span>Incident</span>
          <span>Quartier</span>
          <span>Categorie</span>
          <span>Date</span>
          <span>Statut</span>
        </div>
        <?php foreach ($cityzen['recent_reports'] as $report): ?>
          <div class="table-row">
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
