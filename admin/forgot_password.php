<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

$error = '';
$success = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $error = 'Session expiree : rechargez la page puis reessayez.';
    } else {
        $email = (string) ($_POST['email'] ?? '');
        $pass = (string) ($_POST['pass'] ?? '');
        $pass2 = (string) ($_POST['pass2'] ?? '');

        if ($pass !== $pass2) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            $res = cityzen_reset_user_password_by_email($email, $pass);
            if (($res['ok'] ?? false) === true) {
                $success = 'Mot de passe modifie. Vous pouvez maintenant vous connecter.';
            } else {
                $error = (string) ($res['error'] ?? 'Reinitialisation impossible.');
            }
        }
    }
}

cityzen_render_head('Mot de passe oublie');
?>
<div class="site-shell">
  <header class="topbar topbar-public">
    <div class="brand">
      <span class="brand-dot"></span>
      <span class="brand-text">City <strong>Zen</strong></span>
    </div>
    <nav class="main-nav">
      <a href="<?= htmlspecialchars(cityzen_asset('index.php')) ?>">Accueil public</a>
      <a href="<?= htmlspecialchars(cityzen_asset('admin/login.php')) ?>">Connexion</a>
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

      <form class="login-form" method="post" action="">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
        <label class="login-field">
          <span>Email</span>
          <input type="email" name="email" autocomplete="email" required>
        </label>
        <label class="login-field">
          <span>Nouveau mot de passe</span>
          <input type="password" name="pass" autocomplete="new-password" minlength="8" required>
        </label>
        <label class="login-field">
          <span>Confirmer le nouveau mot de passe</span>
          <input type="password" name="pass2" autocomplete="new-password" minlength="8" required>
        </label>
        <button type="submit" class="login-submit">Reinitialiser</button>
      </form>
      <p class="login-footer-link"><a href="<?= htmlspecialchars(cityzen_asset('admin/login.php')) ?>">Retour a la connexion</a></p>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>
