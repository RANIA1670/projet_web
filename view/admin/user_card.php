<?php

declare(strict_types=1);

cityzen_render_head('Fiche utilisateur');

$scanState = (string) ($scanState ?? 'not_found');
$scannedUser = is_array($scannedUser ?? null) ? $scannedUser : null;
$adminMenu = is_array($cityzen['admin_menu'] ?? null) ? $cityzen['admin_menu'] : [];
$photoUrl = null;
if ($scannedUser !== null) {
    $rawPhoto = trim((string) ($scannedUser['profile_photo'] ?? ''));
    if ($rawPhoto !== '' && str_starts_with($rawPhoto, '/')) {
        $photoUrl = cityzen_asset(ltrim($rawPhoto, '/'));
    }
}
?>
<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <span>City<strong>Zen</strong></span>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($adminMenu as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>">
          <span class="nav-bullet"></span>
          <?= htmlspecialchars((string) ($item['label'] ?? 'Menu')) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <main class="admin-page">
    <header class="admin-header">
      <div>
        <h1>Fiche utilisateur scannee</h1>
        <p class="admin-header-lead">Le QR personnel ouvre ici les informations de base de l&apos;utilisateur.</p>
      </div>
      <div class="admin-user">
        <a class="btn-ghost" href="<?= htmlspecialchars(cityzen_asset('controller/dashboard.php')) ?>">Tableau de bord</a>
        <a class="btn-ghost" href="<?= htmlspecialchars(cityzen_asset('controller/settings.php')) ?>">Parametres</a>
      </div>
    </header>

    <?php if ($scanState === 'forbidden'): ?>
      <section class="panel">
        <h2>Acces refuse</h2>
        <p class="panel-lead">Seuls les agents connectes peuvent ouvrir une fiche utilisateur via un scan QR.</p>
      </section>
    <?php elseif ($scanState === 'not_found' || $scannedUser === null): ?>
      <section class="panel">
        <h2>QR invalide</h2>
        <p class="panel-lead">Le QR scanne ne correspond a aucun utilisateur actif.</p>
      </section>
    <?php else: ?>
      <?php
        $isBlocked = (int) ($scannedUser['blocked'] ?? 0) === 1;
        $statusLabel = $isBlocked ? 'Bloque' : 'Actif';
        $statusClass = $isBlocked ? 'urgent' : 'done';
        $isAdmin = (string) ($scannedUser['role'] ?? 'user') === 'admin';
        $roleLabel = $isAdmin ? 'Administrateur' : 'Citoyen';
        $roleClass = $isAdmin ? 'progress' : 'done';
        $displayName = (string) (($scannedUser['full_name'] ?? '') !== '' ? $scannedUser['full_name'] : ($scannedUser['username'] ?? 'Utilisateur'));
        $username = (string) ($scannedUser['username'] ?? '');
        $email = (string) ($scannedUser['email'] ?? '');
        $phone = (string) ($scannedUser['phone'] ?? '');
        $birthDate = (string) ($scannedUser['birth_date'] ?? '');
        $cityLine = trim((string) (($scannedUser['postal_code'] ?? '') . ' ' . ($scannedUser['city'] ?? '')));
        $createdAt = (string) ($scannedUser['created_at'] ?? '');
        $updatedAt = (string) ($scannedUser['updated_at'] ?? '');
      ?>
      <section class="admin-grid user-card-grid">
        <article class="panel settings-panel">
          <div class="user-card-header">
            <?php if ($photoUrl !== null): ?>
              <img class="avatar avatar-photo user-card-avatar" src="<?= htmlspecialchars($photoUrl) ?>" alt="Photo de l'utilisateur">
            <?php else: ?>
              <span class="avatar avatar-success user-card-avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($username !== '' ? $username : '??', 0, 2))) ?></span>
            <?php endif; ?>

            <div class="user-card-heading">
              <h2><?= htmlspecialchars($displayName) ?></h2>
              <p>@<?= htmlspecialchars($username !== '' ? $username : 'inconnu') ?></p>
            </div>

            <div class="user-card-badges">
              <em class="pill <?= htmlspecialchars($roleClass) ?>"><?= htmlspecialchars($roleLabel) ?></em>
              <em class="pill <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($statusLabel) ?></em>
            </div>
          </div>

          <div class="user-card-fields">
            <div class="users-static-field">
              <span>Email</span>
              <strong><?= htmlspecialchars($email !== '' ? $email : 'Non renseigne') ?></strong>
            </div>
            <div class="users-static-field">
              <span>Telephone</span>
              <strong><?= htmlspecialchars($phone !== '' ? $phone : 'Non renseigne') ?></strong>
            </div>
            <div class="users-static-field">
              <span>Date de naissance</span>
              <strong><?= htmlspecialchars($birthDate !== '' ? $birthDate : 'Non renseignee') ?></strong>
            </div>
            <div class="users-static-field">
              <span>Ville</span>
              <strong><?= htmlspecialchars($cityLine !== '' ? $cityLine : 'Non renseignee') ?></strong>
            </div>
            <div class="users-static-field">
              <span>Inscrit le</span>
              <strong><?= htmlspecialchars($createdAt !== '' ? $createdAt : 'Inconnue') ?></strong>
            </div>
            <div class="users-static-field">
              <span>Derniere mise a jour</span>
              <strong><?= htmlspecialchars($updatedAt !== '' ? $updatedAt : 'Aucune') ?></strong>
            </div>
          </div>
        </article>
      </section>
    <?php endif; ?>
  </main>
</div>
<?php cityzen_render_footer(); ?>
