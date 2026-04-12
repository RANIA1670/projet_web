<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

$error = '';
$next = cityzen_safe_next((string) ($_GET['next'] ?? ''));

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
        $user = trim((string) ($_POST['user'] ?? ''));
        $pass = (string) ($_POST['pass'] ?? '');
        $next = cityzen_safe_next((string) ($_POST['next'] ?? ''));

        $auth = cityzen_authenticate_result($user, $pass);
        if (($auth['ok'] ?? false) === true) {
            $role = (string) ($_SESSION['cityzen_user']['role'] ?? 'user');
            $target = cityzen_post_login_redirect($role, $next);
            header('Location: ' . $target, true, 302);
            exit;
        }

        $error = (string) ($auth['error'] ?? 'Identifiants incorrects.');
    }
}

cityzen_render_head('Connexion');
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
      <p class="login-lead">Les comptes sont ceux crees sur la page d'inscription (fichier local). Les administrateurs accedent au tableau de bord ; les citoyens au site public.</p>

      <?php if ($error !== ''): ?>
        <p class="login-error" role="alert"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form class="login-form" method="post" action="">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
        <label class="login-field">
          <span>Email ou nom d'utilisateur</span>
          <input type="text" name="user" autocomplete="username" required>
        </label>
        <label class="login-field">
          <span>Mot de passe</span>
          <input type="password" name="pass" autocomplete="current-password" required>
        </label>
        <button type="submit" class="login-submit">Se connecter</button>
      </form>
      <p class="login-footer-link"><a href="<?= htmlspecialchars(cityzen_asset('admin/forgot_password.php')) ?>">Mot de passe oublie ?</a></p>

      <p class="login-footer-link">Pas encore de compte ? <a href="<?= htmlspecialchars(cityzen_asset('register.php')) ?>">S'inscrire</a></p>

      <p class="login-hint">Premier lancement : un compte admin demo est cree dans <code>storage/users.json</code> (identifiants historiques <code>agent</code> / <code>cityzen</code> si vous n'avez pas defini les variables d'environnement).</p>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>
