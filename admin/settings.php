<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

cityzen_session_start();
if (!cityzen_is_logged_in()) {
    header('Location: ' . cityzen_login_url(cityzen_asset('admin/settings.php')), true, 302);
    exit;
}

$flashKey = 'cityzen_settings_flash';
$baseUrl = cityzen_asset('admin/settings.php');
$sessionUserId = (int) ($_SESSION['cityzen_user']['id'] ?? 0);

function cityzen_store_profile_photo(array $file, int $userId): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload de la photo echoue.'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => 'Fichier invalide.'];
    }

    if ($size < 1 || $size > 5 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'La photo doit faire moins de 5 Mo.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? (string) finfo_file($finfo, $tmp) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => '',
    };
    if ($ext === '') {
        return ['ok' => false, 'error' => 'Formats acceptes: JPG, PNG, WEBP.'];
    }

    $uploadDir = __DIR__ . '/../storage/uploads/profile';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        return ['ok' => false, 'error' => 'Impossible de creer le dossier d\'upload.'];
    }

    $filename = 'u' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $target = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmp, $target)) {
        return ['ok' => false, 'error' => 'Impossible d\'enregistrer la photo.'];
    }

    return ['ok' => true, 'path' => '/storage/uploads/profile/' . $filename];
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!cityzen_csrf_validate($_POST['csrf'] ?? null)) {
        $_SESSION[$flashKey] = ['type' => 'error', 'msg' => 'Jeton de securite invalide. Rechargez la page.'];
        header('Location: ' . $baseUrl, true, 303);
        exit;
    }

    if ($sessionUserId < 1) {
        $_SESSION[$flashKey] = ['type' => 'error', 'msg' => 'Compte session introuvable.'];
        header('Location: ' . $baseUrl, true, 303);
        exit;
    }

    if (isset($_POST['save_profile'])) {
        $photoPath = null;
        if (isset($_FILES['profile_photo']) && is_array($_FILES['profile_photo'])) {
            $upload = cityzen_store_profile_photo($_FILES['profile_photo'], $sessionUserId);
            if (($upload['ok'] ?? false) !== true) {
                $_SESSION[$flashKey] = ['type' => 'error', 'msg' => (string) ($upload['error'] ?? 'Upload impossible.')];
                header('Location: ' . $baseUrl, true, 303);
                exit;
            }
            $photoPath = isset($upload['path']) && is_string($upload['path']) ? $upload['path'] : null;
        }

        $res = cityzen_update_profile(
            $sessionUserId,
            [
                'full_name' => (string) ($_POST['full_name'] ?? ''),
                'email' => (string) ($_POST['email'] ?? ''),
                'birth_date' => (string) ($_POST['birth_date'] ?? ''),
                'postal_code' => (string) ($_POST['postal_code'] ?? ''),
                'city' => (string) ($_POST['city'] ?? ''),
                'phone' => (string) ($_POST['phone'] ?? ''),
            ],
            $photoPath
        );

        if (($res['ok'] ?? false) === true && isset($res['user']) && is_array($res['user'])) {
            cityzen_apply_session_user($res['user']);
            $_SESSION[$flashKey] = ['type' => 'success', 'msg' => 'Informations mises a jour.'];
        } else {
            $_SESSION[$flashKey] = ['type' => 'error', 'msg' => (string) ($res['error'] ?? 'Mise a jour impossible.')];
        }

        header('Location: ' . $baseUrl, true, 303);
        exit;
    }

    if (isset($_POST['change_password'])) {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($newPassword !== $confirmPassword) {
            $_SESSION[$flashKey] = ['type' => 'error', 'msg' => 'La confirmation du nouveau mot de passe ne correspond pas.'];
            header('Location: ' . $baseUrl, true, 303);
            exit;
        }

        $res = cityzen_change_user_password($sessionUserId, $currentPassword, $newPassword);
        if (($res['ok'] ?? false) === true && isset($res['user']) && is_array($res['user'])) {
            cityzen_apply_session_user($res['user']);
            $_SESSION[$flashKey] = ['type' => 'success', 'msg' => 'Mot de passe modifie.'];
        } else {
            $_SESSION[$flashKey] = ['type' => 'error', 'msg' => (string) ($res['error'] ?? 'Changement du mot de passe impossible.')];
        }

        header('Location: ' . $baseUrl, true, 303);
        exit;
    }
}

$flash = $_SESSION[$flashKey] ?? null;
unset($_SESSION[$flashKey]);

$currentUser = $sessionUserId > 0 ? cityzen_user_get_by_id($sessionUserId) : null;
if ($currentUser === null) {
    cityzen_agent_logout();
    header('Location: ' . cityzen_login_url($baseUrl), true, 302);
    exit;
}

$settingsMenu = cityzen_is_agent()
    ? $cityzen['admin_menu']
    : [
        ['key' => 'home', 'label' => 'Accueil', 'url' => '/index.php'],
        ['key' => 'settings', 'label' => 'Parametres', 'url' => '/admin/settings.php'],
        ['key' => 'logout', 'label' => 'Deconnexion', 'url' => '/admin/logout.php'],
    ];

cityzen_render_head('Parametres');
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
          <a class="btn-ghost" href="<?= htmlspecialchars(cityzen_asset('admin/dashboard.php')) ?>">Tableau de bord</a>
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
            <input type="text" name="full_name" value="<?= htmlspecialchars((string) $currentUser['full_name']) ?>" maxlength="120" placeholder="Votre nom complet">
          </label>
          <label class="users-edit-field">
            <span>Email</span>
            <input type="email" name="email" value="<?= htmlspecialchars((string) $currentUser['email']) ?>" maxlength="190" required placeholder="nom@example.com">
          </label>
          <label class="users-edit-field">
            <span>Date de naissance</span>
            <input type="date" name="birth_date" value="<?= htmlspecialchars((string) $currentUser['birth_date']) ?>" max="<?= htmlspecialchars(date('Y-m-d')) ?>">
          </label>
          <label class="users-edit-field">
            <span>Code postal</span>
            <input type="text" name="postal_code" value="<?= htmlspecialchars((string) $currentUser['postal_code']) ?>" maxlength="20" placeholder="75000">
          </label>
          <label class="users-edit-field">
            <span>Ville</span>
            <input type="text" name="city" value="<?= htmlspecialchars((string) $currentUser['city']) ?>" maxlength="120" placeholder="Paris">
          </label>
          <label class="users-edit-field">
            <span>Numero de telephone</span>
            <input type="text" name="phone" value="<?= htmlspecialchars((string) $currentUser['phone']) ?>" maxlength="30" placeholder="+33 6 12 34 56 78">
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
          </label>
          <label class="users-edit-field">
            <span>Nouveau mot de passe</span>
            <input type="password" name="new_password" autocomplete="new-password" minlength="8" required>
          </label>
          <label class="users-edit-field">
            <span>Confirmer le nouveau mot de passe</span>
            <input type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
          </label>
          <div class="users-edit-actions">
            <button type="submit" class="users-role-submit">Changer le mot de passe</button>
          </div>
        </form>
      </article>
    </section>
  </main>
</div>
<?php cityzen_render_footer(); ?>
