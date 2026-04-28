<?php

declare(strict_types=1);

/** @var string $eq_title */
/** @var string $eq_nav_active */

if (!isset($eq_extra_css) || !is_array($eq_extra_css)) {
    $eq_extra_css = [];
}

require_once dirname(__DIR__, 2) . '/core/layout.php';

global $cityzen;

$styles = array_merge([cityzen_asset('assets/css/equipment_public.css?v=20260428b')], $eq_extra_css);
cityzen_render_head($eq_title, $styles);
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
        <a href="<?= htmlspecialchars($href) ?>" class="<?= ($item['key'] ?? '') === ($eq_nav_active ?? '') ? 'is-active' : '' ?>">
          <?= htmlspecialchars($item['label']) ?>
        </a>
        <?php if (($item['key'] ?? '') === 'equipment' && cityzen_is_logged_in() && cityzen_current_user_id() > 0): ?>
          <a href="<?= htmlspecialchars(cityzen_asset('equipment/my-reservations.php')) ?>" class="<?= ($eq_nav_active ?? '') === 'my-reservations' ? 'is-active' : '' ?>">Mes réservations</a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="topbar-actions">
      <span class="eq-user-pill"><?= htmlspecialchars(cityzen_user_initials()) ?></span>
    </div>
  </header>

  <main class="page public-page eq-public-main">
