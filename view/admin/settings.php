<?php

declare(strict_types=1);

cityzen_render_head('Parametres');
$errors = is_array($_SESSION['form_errors'] ?? null) ? $_SESSION['form_errors'] : [];
$passwordErrors = is_array($_SESSION['password_errors'] ?? null) ? $_SESSION['password_errors'] : [];
$old = is_array($_SESSION['form_old'] ?? null) ? $_SESSION['form_old'] : [];
$qrProfile = is_array($qrProfile ?? null) ? $qrProfile : [];
unset($_SESSION['form_errors'], $_SESSION['password_errors'], $_SESSION['form_old']);
?>
<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <span>City<strong>Zen</strong></span>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($settingsMenu as $item): ?>
        <?php $href = str_starts_with($item['url'], '/') ? cityzen_asset(ltrim($item['url'], '/')) : $item['url']; ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= $item['key'] === 'settings' ? 'is-active' : '' ?>">
          <span class="nav-bullet"></span>
          <?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <main class="admin-page">
    <header class="admin-header">
      <div>
        <h1>Parametres du compte</h1>
        <p class="admin-header-lead">Completez vos informations personnelles et changez votre mot de passe sur cette page dediee.</p>
      </div>
      <div class="admin-user">
        <?php if (cityzen_is_agent()): ?>
          <a class="btn-ghost" href="<?= htmlspecialchars(cityzen_asset('controller/dashboard.php')) ?>">Tableau de bord</a>
        <?php else: ?>
          <a class="btn-ghost" href="<?= htmlspecialchars(cityzen_asset('index.php')) ?>">Accueil</a>
        <?php endif; ?>
        <span><?= htmlspecialchars($cityzen['current_date']) ?></span>
        <?php $avatarUrl = cityzen_user_avatar_url(); ?>
        <?php if ($avatarUrl !== null): ?>
          <img class="avatar avatar-link avatar-photo" src="<?= htmlspecialchars($avatarUrl) ?>" alt="Photo de profil">
        <?php else: ?>
          <span class="avatar avatar-success avatar-link" aria-hidden="true"><?= htmlspecialchars(cityzen_user_initials()) ?></span>
        <?php endif; ?>
      </div>
    </header>

    <?php if (is_array($flash) && isset($flash['msg'])): ?>
      <p class="admin-flash <?= ($flash['type'] ?? '') === 'success' ? 'admin-flash-success' : 'admin-flash-error' ?>" role="status"><?= htmlspecialchars((string) $flash['msg']) ?></p>
    <?php endif; ?>

    <section class="admin-grid settings-grid">
      <article class="panel settings-panel">
        <h2>Mes informations</h2>
        <p class="panel-lead">Ces donnees servent a completer votre fiche sans toucher a votre identifiant de connexion.</p>
        <form class="users-edit-form settings-form" method="post" action="" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
          <input type="hidden" name="save_profile" value="1">
          <div class="users-static-field">
            <span>Nom d&apos;utilisateur</span>
            <strong><?= htmlspecialchars((string) $currentUser['username']) ?></strong>
          </div>
          <label class="users-edit-field">
            <span>Nom complet</span>
            <input type="text" name="full_name" value="<?= htmlspecialchars((string) ($old['full_name'] ?? $currentUser['full_name'])) ?>" maxlength="120" placeholder="Votre nom complet">
            <?php if (isset($errors['full_name'])): ?><small class="users-edit-error"><?= htmlspecialchars((string) $errors['full_name']) ?></small><?php endif; ?>
          </label>
          <label class="users-edit-field">
            <span>Email</span>
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($old['email'] ?? $currentUser['email'])) ?>" maxlength="190" required placeholder="nom@example.com">
            <?php if (isset($errors['email'])): ?><small class="users-edit-error"><?= htmlspecialchars((string) $errors['email']) ?></small><?php endif; ?>
          </label>
          <label class="users-edit-field">
            <span>Date de naissance</span>
            <input type="date" name="birth_date" value="<?= htmlspecialchars((string) ($old['birth_date'] ?? $currentUser['birth_date'])) ?>" max="<?= htmlspecialchars(date('Y-m-d')) ?>">
            <?php if (isset($errors['birth_date'])): ?><small class="users-edit-error"><?= htmlspecialchars((string) $errors['birth_date']) ?></small><?php endif; ?>
          </label>
          <label class="users-edit-field">
            <span>Code postal</span>
            <input type="text" name="postal_code" value="<?= htmlspecialchars((string) ($old['postal_code'] ?? $currentUser['postal_code'])) ?>" maxlength="20" placeholder="75000">
            <?php if (isset($errors['postal_code'])): ?><small class="users-edit-error"><?= htmlspecialchars((string) $errors['postal_code']) ?></small><?php endif; ?>
          </label>
          <label class="users-edit-field">
            <span>Ville</span>
            <input type="text" name="city" value="<?= htmlspecialchars((string) ($old['city'] ?? $currentUser['city'])) ?>" maxlength="120" placeholder="Paris">
            <?php if (isset($errors['city'])): ?><small class="users-edit-error"><?= htmlspecialchars((string) $errors['city']) ?></small><?php endif; ?>
          </label>
          <label class="users-edit-field">
            <span>Numero de telephone</span>
            <input type="text" name="phone" value="<?= htmlspecialchars((string) ($old['phone'] ?? $currentUser['phone'])) ?>" maxlength="30" placeholder="+33 6 12 34 56 78">
            <?php if (isset($errors['phone'])): ?><small class="users-edit-error"><?= htmlspecialchars((string) $errors['phone']) ?></small><?php endif; ?>
          </label>
          <label class="users-edit-field">
            <span>Photo de profil (JPG, PNG, WEBP, max 5 Mo)</span>
            <input type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
          </label>
          <div class="users-edit-actions">
            <button type="submit" class="users-role-submit">Enregistrer mes informations</button>
          </div>
        </form>
      </article>

      <article class="panel settings-panel">
        <h2>Changer le mot de passe</h2>
        <p class="panel-lead">Le mot de passe se modifie ici, sur une page separee de la gestion des autres utilisateurs.</p>
        <form class="users-edit-form settings-form" method="post" action="">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
          <input type="hidden" name="change_password" value="1">
          <label class="users-edit-field">
            <span>Mot de passe actuel</span>
            <input type="password" name="current_password" autocomplete="current-password" required>
            <?php if (isset($passwordErrors['current_password'])): ?><small class="users-edit-error"><?= htmlspecialchars((string) $passwordErrors['current_password']) ?></small><?php endif; ?>
          </label>
          <label class="users-edit-field">
            <span>Nouveau mot de passe</span>
            <input type="password" name="new_password" autocomplete="new-password" minlength="8" required>
            <?php if (isset($passwordErrors['new_password'])): ?><small class="users-edit-error"><?= htmlspecialchars((string) $passwordErrors['new_password']) ?></small><?php endif; ?>
          </label>
          <label class="users-edit-field">
            <span>Confirmer le nouveau mot de passe</span>
            <input type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
            <?php if (isset($passwordErrors['confirm_password'])): ?><small class="users-edit-error"><?= htmlspecialchars((string) $passwordErrors['confirm_password']) ?></small><?php endif; ?>
          </label>
          <div class="users-edit-actions">
            <button type="submit" class="users-role-submit">Changer le mot de passe</button>
          </div>
        </form>
      </article>

      <article class="panel settings-panel qr-profile-panel">
        <h2>Mon QR personnel</h2>
        <p class="panel-lead">Chaque utilisateur possede un QR personnel. Quand un agent le scanne, il ouvre directement votre fiche utilisateur.</p>

        <?php if (($qrProfile['ok'] ?? false) === true): ?>
          <div class="qr-profile-card">
            <div class="qr-code-frame">
              <img src="<?= htmlspecialchars((string) $qrProfile['image_url']) ?>" alt="QR code du profil utilisateur">
            </div>

            <div class="qr-profile-meta">
              <div class="users-static-field">
                <span>Destination du scan</span>
                <strong>Fiche utilisateur agent</strong>
              </div>

              <div class="users-static-field">
                <span>Acces direct</span>
                <strong><a class="data-link" href="<?= htmlspecialchars((string) $qrProfile['target_url']) ?>" target="_blank" rel="noopener">Tester le lien du QR</a></strong>
              </div>

              <p class="qr-profile-note">Le QR contient un jeton technique (pas vos donnees en clair). Si l&apos;agent n&apos;est pas connecte, il sera redirige vers la connexion.</p>
            </div>
          </div>
        <?php else: ?>
          <p class="admin-flash admin-flash-error" role="alert"><?= htmlspecialchars((string) ($qrProfile['error'] ?? 'QR indisponible.')) ?></p>
        <?php endif; ?>
      </article>
    </section>
  </main>
</div>
<style>
.users-edit-error {
  display: block;
  margin-top: 4px;
  color: #dc3545;
  font-size: 0.875rem;
  font-weight: 500;
}
.users-edit-field input:invalid {
  border-color: #dc3545;
}
</style>
<?php cityzen_render_footer(); ?>
