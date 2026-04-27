<?php

declare(strict_types=1);

cityzen_render_head('Mot de passe oublie');
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
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
    <section class="login-panel">
      <h1>Mot de passe oublie</h1>
      <p class="login-lead">Entrez votre email et votre nouveau mot de passe pour recuperer l&apos;acces.</p>

      <?php if ($error !== ''): ?>
        <p class="login-error" role="alert"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <?php if ($success !== ''): ?>
        <p class="admin-flash admin-flash-success" role="status"><?= htmlspecialchars($success) ?></p>
      <?php endif; ?>

      <form class="login-form" method="post" action="" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
        <label class="login-field">
          <span>Email</span>
          <input type="email" name="email" autocomplete="email" required value="<?= htmlspecialchars((string) ($old['email'] ?? '')) ?>">
          <?php if (isset($errors['email'])): ?><small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['email']) ?></small><?php endif; ?>
        </label>
        <label class="login-field">
          <span>Nouveau mot de passe</span>
          <input type="password" name="pass" autocomplete="new-password" minlength="8" required>
          <?php if (isset($errors['pass'])): ?><small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['pass']) ?></small><?php endif; ?>
        </label>
        <label class="login-field">
          <span>Confirmer le nouveau mot de passe</span>
          <input type="password" name="pass2" autocomplete="new-password" minlength="8" required>
          <?php if (isset($errors['pass2'])): ?><small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['pass2']) ?></small><?php endif; ?>
        </label>
        <button type="submit" class="login-submit">Reinitialiser</button>
      </form>
      <p class="login-footer-link"><a href="<?= htmlspecialchars(cityzen_asset('controller/login.php')) ?>">Retour a la connexion</a></p>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>

