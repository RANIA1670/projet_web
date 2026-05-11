<?php

declare(strict_types=1);

/**
 * Enveloppe publique CityZen (navbar identique à l’accueil) pour les pages forum.
 */

function cityzen_forum_public_shell_start(
    string $pageTitle = 'Forum',
    array $extraStyles = [],
    string $activeNavKey = 'forum'
): void {
    cityzen_render_head($pageTitle, $extraStyles);

    $cz = $GLOBALS['cityzen'] ?? [];
    $menu = is_array($cz['public_menu'] ?? null) ? $cz['public_menu'] : [];
    ?>
    <div class="site-shell">
      <header class="topbar topbar-public">
        <div class="brand">
          <span class="brand-dot"></span>
          <span class="brand-text">City <strong>Zen</strong></span>
        </div>
        <nav class="main-nav">
          <?php foreach (cityzen_full_public_nav($menu) as $item): ?>
            <?php
            $href = str_starts_with((string) ($item['url'] ?? ''), '/')
                ? cityzen_asset(ltrim((string) $item['url'], '/'))
                : (string) ($item['url'] ?? '#');
            ?>
            <a href="<?= htmlspecialchars($href) ?>" class="<?= ($item['key'] ?? '') === $activeNavKey ? 'is-active' : '' ?>">
              <?= htmlspecialchars((string) ($item['label'] ?? '')) ?>
            </a>
            <?php if (($item['key'] ?? '') === 'equipment' && cityzen_is_logged_in() && cityzen_current_user_id() > 0): ?>
              <a href="<?= htmlspecialchars(cityzen_asset('equipment/my-reservations.php')) ?>">Mes réservations</a>
            <?php endif; ?>
          <?php endforeach; ?>
        </nav>
        <div class="topbar-actions">
          <?php if (cityzen_is_logged_in()): ?>
            <?php $avatarUrl = cityzen_user_avatar_url(); ?>
            <a href="<?= htmlspecialchars(cityzen_asset('controller/settings.php')) ?>" aria-label="Ouvrir les parametres">
              <?php if ($avatarUrl !== null): ?>
                <img class="avatar avatar-link avatar-photo" src="<?= htmlspecialchars($avatarUrl) ?>" alt="Photo de profil">
              <?php else: ?>
                <span class="avatar avatar-success avatar-link"><?= htmlspecialchars(cityzen_user_initials()) ?></span>
              <?php endif; ?>
            </a>
          <?php else: ?>
            <a class="avatar avatar-success avatar-link" href="<?= htmlspecialchars(cityzen_asset('controller/login.php')) ?>" aria-label="Connexion">IN</a>
          <?php endif; ?>
        </div>
      </header>
      <main class="page public-page forum-embedded">
    <?php
}

function cityzen_forum_public_shell_end(): void
{
    ?>
      </main>
    </div>
    <?php
    cityzen_render_footer();
}
