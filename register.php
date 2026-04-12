<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

$error = '';

if (cityzen_is_logged_in()) {
    if (cityzen_is_agent()) {
        header('Location: ' . cityzen_asset('admin/dashboard.php'), true, 302);
    } else {
        header('Location: ' . cityzen_asset('index.php'), true, 302);
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $error = 'Session expiree : rechargez la page puis reessayez.';
    } else {
        $fullName = (string) ($_POST['full_name'] ?? '');
        $email = (string) ($_POST['email'] ?? '');
        $pass = (string) ($_POST['pass'] ?? '');
        $pass2 = (string) ($_POST['pass2'] ?? '');

        if ($pass !== $pass2) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            $result = cityzen_register_user_with_email($email, $pass, $fullName);

            if (!$result['ok']) {
                $error = (string) ($result['error'] ?? 'Inscription refusee.');
            } else {
                cityzen_apply_session_user($result['user']);
                header('Location: ' . cityzen_asset('index.php'), true, 302);
                exit;
            }
        }
    }
}

cityzen_render_head('Creer un compte');
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
    <section class="login-panel login-panel-wide">
      <h1>Creer un compte</h1>
      <p class="login-lead">Tout nouveau compte est un <strong>compte citoyen</strong> (acces au site public). Un administrateur peut promouvoir un compte en <strong>admin</strong> depuis le tableau de bord.</p>

      <?php if ($error !== ''): ?>
        <p class="login-error" role="alert"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form class="login-form" method="post" action="">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
        <label class="login-field">
          <span>Nom complet</span>
          <input type="text" name="full_name" autocomplete="name" maxlength="120" required>
        </label>
        <label class="login-field">
          <span>Email</span>
          <input type="email" name="email" autocomplete="email" maxlength="190" required>
        </label>
        <label class="login-field">
          <span>Mot de passe</span>
          <input type="password" name="pass" autocomplete="new-password" minlength="8" required>
        </label>
        <label class="login-field">
          <span>Confirmer le mot de passe</span>
          <input type="password" name="pass2" autocomplete="new-password" minlength="8" required>
        </label>

        <button type="submit" class="login-submit">S'inscrire</button>
      </form>

      <p class="login-hint">L&apos;email est unique : un meme email ne peut pas creer plusieurs comptes. Le role <strong>admin</strong> ne s&apos;obtient pas a l&apos;inscription.</p>

      <p class="login-footer-link">Deja un compte ? <a href="<?= htmlspecialchars(cityzen_asset('admin/login.php')) ?>">Se connecter</a></p>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>
