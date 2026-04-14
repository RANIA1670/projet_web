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
      <?php foreach ($cityzen['public_menu'] as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= $item['key'] === 'home' ? 'is-active' : '' ?>">
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
    <section class="hero">
      <p class="hero-kicker">SIGNALEMENT - PARTICIPATION - OPEN DATA</p>
      <h1>Votre ville, <span>vos actions</span></h1>
      <p class="hero-copy">Signalez un incident, votez pour un projet, suivez l'etat de votre quartier.</p>

      <form class="search-bar" data-search-form>
        <input type="search" name="q" placeholder="Que signaler ou chercher ?" data-search-input>
        <button type="submit">Rechercher</button>
      </form>
    </section>

    <section class="stats-row">
      <article class="stat-card">
        <strong><?= htmlspecialchars((string) $cityzen['stats']['reports']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['reports']['label']) ?></span>
      </article>
      <article class="stat-card">
        <strong class="accent-orange"><?= htmlspecialchars((string) $cityzen['stats']['projects']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['projects']['label']) ?></span>
      </article>
      <article class="stat-card">
        <strong><?= htmlspecialchars($cityzen['stats']['resolution']['value']) ?></strong>
        <span><?= htmlspecialchars($cityzen['stats']['resolution']['label']) ?></span>
      </article>
    </section>

    <section class="services-grid">
      <?php foreach ($cityzen['services'] as $service): ?>
        <article class="service-card <?= $service['highlight'] ? 'is-highlighted' : '' ?>">
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
        <a href="<?= htmlspecialchars(cityzen_asset('admin/index.php')) ?>">Voir tout -></a>
      </div>

      <div class="report-list" data-report-list>
        <?php foreach ($cityzen['recent_reports'] as $report): ?>
          <article class="report-item" data-report-item data-search-text="<?= htmlspecialchars(mb_strtolower($report['title'] . ' ' . $report['meta'] . ' ' . $report['category'])) ?>">
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
      <article class="info-card" id="open-data">
        <h2>Open Data</h2>
        <p>Les donnees du tableau de bord sont exposees en JSON pour faciliter l'integration avec d'autres services municipaux.</p>
        <a class="data-link" href="<?= htmlspecialchars(cityzen_asset('api/dashboard.php')) ?>">Voir l'API dashboard</a>
      </article>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>
