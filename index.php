<?php

require_once __DIR__ . '/includes/layout.php';

cityzen_render_head('Accueil citoyen');
?>
<div class="site-shell">
  <header class="topbar topbar-public">
    <div class="brand">
      <span class="brand-dot"></span>
      <span class="brand-text">City <strong>Zen</strong></span>
    </div>
    <nav class="main-nav">
      <?php foreach (cityzen_full_public_nav($cityzen['public_menu']) as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= $item['key'] === 'home' ? 'is-active' : '' ?>">
          <?= htmlspecialchars($item['label']) ?>
        </a>
        <?php if ($item['key'] === 'equipment' && cityzen_is_logged_in() && cityzen_current_user_id() > 0): ?>
          <a href="<?= htmlspecialchars(cityzen_asset('equipment/my-reservations.php')) ?>">Mes réservations</a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="topbar-actions">
      <button class="avatar avatar-warning" type="button">O</button>
      <button class="avatar avatar-success" type="button"><?= htmlspecialchars(cityzen_user_initials()) ?></button>
    </div>
  </header>

  <main class="page public-page">
    <section class="hero">
      <p class="hero-kicker">SIGNALEMENT - PARTICIPATION - EQUIPEMENT</p>
      <h1>Votre ville, <span>vos actions</span></h1>
      <p class="hero-copy">Signalez un incident, votez pour un projet, suivez l'etat de votre quartier. Le <a href="<?= htmlspecialchars(cityzen_asset('admin/index.php')) ?>">portail municipal</a> prolonge ces services.
        <?php if (cityzen_is_agent()): ?>
          Le <a href="<?= htmlspecialchars(cityzen_asset('admin/dashboard.php')) ?>">tableau de bord back-office</a> est disponible pour votre session.
        <?php else: ?>
          L'<a href="<?= htmlspecialchars(cityzen_login_url(cityzen_asset('admin/dashboard.php'))) ?>">espace agents</a> (connexion) permet d'acceder au back-office.
        <?php endif; ?>
      </p>

      <form class="search-bar" data-search-form>
        <input type="search" name="q" placeholder="Que signaler ou chercher ?" data-search-input>
        <button type="submit">Rechercher</button>
      </form>

      <div class="quick-filters" data-filter-group>
        <button type="button" class="filter-chip is-active" data-filter="all">Tous</button>
        <button type="button" class="filter-chip" data-filter="urgent">Urgents</button>
        <button type="button" class="filter-chip" data-filter="progress">En cours</button>
        <button type="button" class="filter-chip" data-filter="new">Nouveaux</button>
      </div>
    </section>

    <section class="stats-row">
      <article class="stat-card interactive-card" data-card>
        <strong data-count-to="<?= htmlspecialchars((string) $cityzen['stats']['reports']['value']) ?>"><?= htmlspecialchars((string) $cityzen['stats']['reports']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['reports']['label']) ?></span>
      </article>
      <article class="stat-card interactive-card" data-card>
        <strong class="accent-orange" data-count-to="<?= htmlspecialchars((string) $cityzen['stats']['projects']['value']) ?>"><?= htmlspecialchars((string) $cityzen['stats']['projects']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['projects']['label']) ?></span>
      </article>
      <article class="stat-card interactive-card" data-card>
        <strong data-count-to="<?= htmlspecialchars(rtrim($cityzen['stats']['resolution']['value'], '%')) ?>" data-count-suffix="%"><?= htmlspecialchars($cityzen['stats']['resolution']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['resolution']['label']) ?></span>
      </article>
    </section>

    <section class="services-grid">
      <?php foreach ($cityzen['services'] as $service): ?>
        <article class="service-card interactive-card <?= $service['highlight'] ? 'is-highlighted' : '' ?>" data-card tabindex="0">
          <div class="service-icon"><?= htmlspecialchars($service['icon']) ?></div>
          <h2><?= strtoupper(htmlspecialchars($service['title'])) ?></h2>
          <p><?= htmlspecialchars($service['description']) ?></p>
          <?php if ($service['status'] !== ''): ?>
            <span class="pill success"><?= htmlspecialchars($service['status']) ?></span>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="feed-section" id="signalements">
      <div class="section-header">
        <h2>Signalements recents</h2>
        <div class="section-header-links">
          <a href="<?= htmlspecialchars(cityzen_asset('admin/index.php')) ?>">Portail municipal</a>
          <?php if (cityzen_is_agent()): ?>
            <span class="section-header-sep" aria-hidden="true">|</span>
            <a href="<?= htmlspecialchars(cityzen_asset('admin/dashboard.php')) ?>">Vue agents</a>
          <?php else: ?>
            <span class="section-header-sep" aria-hidden="true">|</span>
            <a href="<?= htmlspecialchars(cityzen_login_url(cityzen_asset('admin/dashboard.php'))) ?>">Connexion</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="report-list" data-report-list>
        <?php foreach ($cityzen['recent_reports'] as $report): ?>
          <article class="report-item" data-report-item data-status="<?= htmlspecialchars($report['severity']) ?>" data-search-text="<?= htmlspecialchars(mb_strtolower($report['title'] . ' ' . $report['meta'] . ' ' . $report['category'])) ?>">
            <div class="report-icon <?= htmlspecialchars($report['severity']) ?>">
              <?= htmlspecialchars(cityzen_icon($report['severity'])) ?>
            </div>
            <div class="report-copy">
              <h3><?= htmlspecialchars($report['title']) ?></h3>
              <p><?= htmlspecialchars($report['meta']) ?></p>
            </div>
            <div class="report-status">
              <strong class="<?= htmlspecialchars($report['severity']) ?>"><?= htmlspecialchars($report['status']) ?></strong>
              <span class="report-dots">.....</span>
            </div>
            <button type="button" class="report-toggle" data-report-toggle aria-expanded="false">Details</button>
            <div class="report-details" data-report-details hidden>
              <p><strong>Quartier:</strong> <?= htmlspecialchars($report['district']) ?></p>
              <p><strong>Categorie:</strong> <?= htmlspecialchars($report['category']) ?></p>
              <p><strong>Date:</strong> <?= htmlspecialchars($report['date']) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <p class="empty-state" data-empty-state hidden>Aucun resultat pour cette recherche.</p>
    </section>

    <section class="info-strip">
      <article class="info-card" id="projets">
        <h2>Projets citoyens</h2>
        <p>38 projets sont actuellement soumis au vote: mobilite, eclairage, espaces verts et modernisation des quartiers.</p>
      </article>
      <article class="info-card" id="equipment">
        <h2>Équipement municipal</h2>
        <p>Consultez le matériel mis à disposition, les lieux et les créneaux, puis déposez une demande de réservation en ligne (validation par un agent).</p>
        <a class="data-link" href="<?= htmlspecialchars(cityzen_asset('equipment/index.php')) ?>">Voir le catalogue équipement</a>
      </article>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>