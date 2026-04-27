<?php

declare(strict_types=1);

cityzen_render_head('Creer un compte');
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$qrGate = is_array($qrGate ?? null) ? $qrGate : [];
$qrScanUrl = (string) ($qrGate['scan_url'] ?? '');
$qrImageUrl = (string) ($qrGate['image_url'] ?? '');
$qrValidated = ($qrGate['validated'] ?? false) === true;
$qrValidationError = (string) ($qrGate['validation_error'] ?? '');
?>
<div class="site-shell">
  <header class="topbar topbar-public">
    <div class="brand">
      <span class="brand-dot"></span>
      <span class="brand-text">City <strong>Zen</strong></span>
    </div>
    <nav class="main-nav">
      <a href="<?= htmlspecialchars(cityzen_asset('index.php')) ?>">Accueil public</a>
      <a href="<?= htmlspecialchars(cityzen_asset('controller/login.php')) ?>">Connexion</a>
    </nav>
  </header>

  <main class="page public-page">
    <section class="login-panel login-panel-wide">
      <h1>Creer un compte</h1>
      <p class="login-lead">Tout nouveau compte est un <strong>compte citoyen</strong> (acces au site public). Un administrateur peut promouvoir un compte en <strong>admin</strong> depuis le tableau de bord.</p>

      <div class="qr-profile-card">
        <div class="qr-code-frame">
          <?php if ($qrImageUrl !== ''): ?>
            <img src="<?= htmlspecialchars($qrImageUrl) ?>" alt="QR de validation avant inscription">
          <?php endif; ?>
        </div>
        <div class="qr-profile-meta">
          <p class="qr-profile-note">Avant la creation du compte, scannez ce QR de validation.</p>
          <?php if ($qrValidated): ?>
            <p class="login-hint"><strong>QR valide :</strong> vous pouvez finaliser l'inscription.</p>
          <?php else: ?>
            <p class="login-hint"><strong>QR requis :</strong> la creation du compte est bloquee tant que le QR n'est pas scanne.</p>
          <?php endif; ?>
          <?php if ($qrValidationError !== ''): ?>
            <p class="login-error" role="alert"><?= htmlspecialchars($qrValidationError) ?></p>
          <?php endif; ?>
          <?php if ($qrScanUrl !== ''): ?>
            <div class="register-qr-actions">
              <a class="users-admin-cta" href="<?= htmlspecialchars($qrScanUrl) ?>">J&apos;ai scanne le QR</a>
              <a class="btn-ghost" href="<?= htmlspecialchars($qrScanUrl) ?>" target="_blank" rel="noopener">Ouvrir le lien QR</a>
            </div>
            <p class="login-hint">Lien du QR: <a href="<?= htmlspecialchars($qrScanUrl) ?>"><?= htmlspecialchars($qrScanUrl) ?></a></p>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($error !== ''): ?>
        <p class="login-error" role="alert"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form class="login-form" method="post" action="" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
        <?php if (isset($errors['qr'])): ?><small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['qr']) ?></small><?php endif; ?>
        <label class="login-field">
          <span>Nom complet</span>
          <input type="text" name="full_name" autocomplete="name" maxlength="120" required value="<?= htmlspecialchars((string) ($old['full_name'] ?? '')) ?>">
          <?php if (isset($errors['full_name'])): ?><small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['full_name']) ?></small><?php endif; ?>
        </label>
        <label class="login-field">
          <span>Email</span>
          <input type="email" name="email" autocomplete="email" maxlength="190" required value="<?= htmlspecialchars((string) ($old['email'] ?? '')) ?>">
          <?php if (isset($errors['email'])): ?><small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['email']) ?></small><?php endif; ?>
        </label>
        <label class="login-field">
          <span>Mot de passe</span>
          <input type="password" name="pass" autocomplete="new-password" minlength="8" required>
          <?php if (isset($errors['pass'])): ?><small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['pass']) ?></small><?php endif; ?>
        </label>
        <label class="login-field">
          <span>Confirmer le mot de passe</span>
          <input type="password" name="pass2" autocomplete="new-password" minlength="8" required>
          <?php if (isset($errors['pass2'])): ?><small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['pass2']) ?></small><?php endif; ?>
        </label>

        <button type="submit" class="login-submit">S'inscrire</button>
      </form>

      <p class="login-hint">L&apos;email est unique : un meme email ne peut pas creer plusieurs comptes. Le role <strong>admin</strong> ne s&apos;obtient pas a l&apos;inscription.</p>
      <p class="login-hint">Si vous etes deja connecte, deconnectez-vous d&apos;abord pour revenir a cet ecran QR d&apos;inscription.</p>

      <p class="login-footer-link">Deja un compte ? <a href="<?= htmlspecialchars(cityzen_asset('controller/login.php')) ?>">Se connecter</a></p>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>

