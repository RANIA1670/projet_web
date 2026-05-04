<?php

declare(strict_types=1);

cityzen_render_head('Connexion');
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
      <a href="<?= htmlspecialchars(cityzen_asset('register.php')) ?>">Creer un compte</a>
    </nav>
  </header>

  <main class="page public-page">
    <section class="login-panel">
      <h1>Connexion</h1>
      <p class="login-lead">Les comptes sont ceux crees sur la page d'inscription. Les administrateurs accedent au tableau de bord ; les citoyens au site public.</p>

      <?php if ($error !== ''): ?>
        <p class="login-error" role="alert"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form class="login-form" method="post" action="" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
        <label class="login-field">
          <span>Email, nom d'utilisateur ou nom complet</span>
          <input type="text" name="user" autocomplete="username" required value="<?= htmlspecialchars((string) ($old['user'] ?? '')) ?>">
          <?php if (isset($errors['user'])): ?><small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['user']) ?></small><?php endif; ?>
        </label>
        <label class="login-field">
          <span>Mot de passe</span>
          <input type="password" name="pass" autocomplete="current-password" required>
          <?php if (isset($errors['pass'])): ?><small class="login-error" role="alert"><?= htmlspecialchars((string) $errors['pass']) ?></small><?php endif; ?>
        </label>
        <button type="submit" class="login-submit">Se connecter</button>
      </form>
      <p class="login-footer-link"><a href="<?= htmlspecialchars(cityzen_asset('controller/forgot_password_new.php')) ?>">Mot de passe oublie ?</a></p>

      <p class="login-footer-link">Pas encore de compte ? <a href="<?= htmlspecialchars(cityzen_asset('register.php')) ?>">S'inscrire</a></p>

      <p class="login-hint">Astuce : vous pouvez vous connecter avec votre email, votre nom d'utilisateur, ou votre nom complet exactement comme enregistre.</p>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>
