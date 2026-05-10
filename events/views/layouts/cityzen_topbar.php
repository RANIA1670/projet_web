<?php
declare(strict_types=1);

/**
 * Barre citoyenne identique au portail (`view/public/home.php`) pour navigation fluide.
 *
 * @var string $cityzen_nav_active clé entrée `$cityzen['public_menu']` à souligner — défaut « events_gestion »
 */

if (!isset($GLOBALS['cityzen']) || !is_array($GLOBALS['cityzen'])) {
    require_once dirname(__DIR__, 3) . '/core/data.php';
}
$cityzen = $GLOBALS['cityzen'];

$cityzen_nav_active ??= 'events_gestion';
$czActiveKey = is_string($cityzen_nav_active) ? $cityzen_nav_active : 'events_gestion';
?>
<header class="topbar topbar-public">
    <div class="brand">
      <span class="brand-dot"></span>
      <span class="brand-text">City <strong>Zen</strong></span>
    </div>
    <nav class="main-nav">
      <?php foreach (cityzen_full_public_nav($cityzen['public_menu']) as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="<?= ($item['key'] ?? '') === $czActiveKey ? 'is-active' : '' ?>">
          <?= htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php if (($item['key'] ?? '') === 'equipment' && cityzen_is_logged_in() && cityzen_current_user_id() > 0): ?>
          <a href="<?= htmlspecialchars(cityzen_asset('equipment/my-reservations.php'), ENT_QUOTES, 'UTF-8') ?>">Mes réservations</a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="topbar-actions">
      <?php if (cityzen_is_logged_in()): ?>
        <?php $avatarUrl = cityzen_user_avatar_url(); ?>
        <a href="<?= htmlspecialchars(cityzen_asset('controller/settings.php'), ENT_QUOTES, 'UTF-8') ?>" aria-label="Ouvrir les parametres">
          <?php if ($avatarUrl !== null): ?>
            <img class="avatar avatar-link avatar-photo" src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Photo de profil">
          <?php else: ?>
            <span class="avatar avatar-success avatar-link"><?= htmlspecialchars(cityzen_user_initials(), ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </a>
      <?php else: ?>
        <a class="avatar avatar-success avatar-link" href="<?= htmlspecialchars(cityzen_asset('controller/login.php'), ENT_QUOTES, 'UTF-8') ?>" aria-label="Connexion">IN</a>
      <?php endif; ?>
    </div>
</header>
