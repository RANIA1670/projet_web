<?php
declare(strict_types=1);

if (!function_exists('cityzen_render_head')) {
    require_once dirname(__DIR__, 4) . '/core/layout.php';
}

$citizenLogoutHref = htmlspecialchars(cityzen_asset('controller/logout.php'), ENT_QUOTES, 'UTF-8');
$citizenPortalHref = htmlspecialchars(cityzen_asset('controller/index.php'), ENT_QUOTES, 'UTF-8');
$intervPublicHref = htmlspecialchars(rtrim(APP_URL, '/') . '/', ENT_QUOTES, 'UTF-8');

cityzen_render_head(
    (string) ($pageTitle ?? 'Administration interventions'),
    [
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
    ],
    'interventions-bo-body'
);
?>
<style>
  :root {
    --sidebar-w: 260px;
    --bg: #F4F6F9; --surface: #FFFFFF; --surface2: #F1F3F5; --border: #E8ECF0;
    --accent: #27AE60; --secondary: #2C3E50; --accent2: #E67E22;
    --danger: #E74C3C; --success: #2ECC71; --warning: #F1C40F;
    --text: #2C3E50; --text-muted: #95A5A6; --white: #FFFFFF; --radius: 12px;
  }
  body.interventions-bo-body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
  .site-shell-interventions-bo { min-height: 100vh; }
  .interventions-bo-stage { padding-top: 14px; }
  .interv-bo-toplinks { margin-bottom: 14px; display: flex; flex-wrap: wrap; gap: 8px 14px; font-size: 12px; }
  .interv-bo-toplinks a { color: #1a73e8; text-decoration: none; font-weight: 600; }
  .interv-bo-toplinks a:hover { text-decoration: underline; }

  .admin-layout { display: flex; min-height: 70vh; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
  .admin-sidebar { width: var(--sidebar-w); background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; flex-shrink: 0; }
  .sidebar-brand { padding: 24px 20px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
  .sidebar-brand .brand-icon { width: 40px; height: 40px; background: linear-gradient(135deg, var(--secondary), var(--accent)); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: var(--white); }
  .sidebar-brand .brand-name { font-size: 18px; font-weight: 700; }
  .sidebar-brand .brand-sub { font-size: 10px; color: var(--text-muted); display: block; letter-spacing: 1px; text-transform: uppercase; }
  .sidebar-nav { padding: 16px 0; flex: 1; }
  .nav-section-label { font-size: 10px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: var(--text-muted); padding: 12px 20px 6px; }
  .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 11px 20px; color: var(--text-muted); text-decoration: none; border-left: 3px solid transparent; font-weight: 500; transition: all .2s; }
  .sidebar-link:hover { color: var(--text); background: rgba(39,174,96,.08); border-left-color: var(--accent); }
  .sidebar-link.active { color: var(--accent); background: rgba(39,174,96,.12); border-left-color: var(--accent); }
  .sidebar-footer { padding: 16px 20px; border-top: 1px solid var(--border); }
  .admin-user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
  .admin-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent2)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; }
  .admin-user-name { font-weight: 600; font-size: 13px; }
  .admin-user-role { font-size: 11px; color: var(--text-muted); }
  .btn-front, .btn-logout { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: var(--radius); text-decoration: none; font-size: 13px; font-weight: 600; width: 100%; }
  .btn-front { background: rgba(39,174,96,.12); color: var(--accent); margin-bottom: 8px; }
  .btn-front:hover { background: rgba(39,174,96,.2); }
  .btn-logout { background: rgba(231,76,60,.08); color: var(--danger); }
  .btn-logout:hover { background: rgba(231,76,60,.16); }

  .admin-main { flex: 1; display: flex; flex-direction: column; min-width: 0; background: var(--bg); }
  .admin-topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 28px; height: 60px; display: flex; align-items: center; justify-content: space-between; }
  .topbar-title { font-size: 16px; font-weight: 600; }
  .topbar-breadcrumb { font-size: 12px; color: var(--text-muted); }
  .topbar-breadcrumb span { color: var(--accent); }
  .topbar-badge { background: var(--surface2); border: 1px solid var(--border); border-radius: 20px; padding: 4px 12px; font-size: 12px; color: var(--text-muted); }
  .flash-admin { margin: 20px 28px 0; padding: 12px 18px; border-radius: var(--radius); display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 500; }
  .flash-admin.flash-success { background: rgba(46,213,115,.12); border: 1px solid rgba(46,213,115,.3); color: var(--success); }
  .flash-admin.flash-error { background: rgba(255,71,87,.12); border: 1px solid rgba(255,71,87,.3); color: var(--danger); }
  .flash-admin.flash-info { background: rgba(108,99,255,.12); border: 1px solid rgba(108,99,255,.3); color: var(--accent); }
  .admin-content { padding: 28px; flex: 1; }

  @media (max-width: 900px) {
    .admin-layout { flex-direction: column; }
    .admin-sidebar { width: 100%; border-right: 0; border-bottom: 1px solid var(--border); }
  }
</style>

<div class="site-shell site-shell-interventions-bo">
<?php
$cityzen_nav_active = 'interventions_gestion';
include dirname(__DIR__, 4) . '/events/views/layouts/cityzen_topbar.php';
?>

<main class="page public-page interventions-bo-stage">
  <div class="interv-bo-toplinks">
    <a href="<?= htmlspecialchars(cityzen_asset('index.php'), ENT_QUOTES, 'UTF-8') ?>">← Portail CityZen</a>
    <a href="<?= $intervPublicHref ?>">Accueil interventions</a>
    <a href="<?= htmlspecialchars(cityzen_asset('controller/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>">Tableau de bord agents</a>
  </div>

  <div class="admin-layout">
    <aside class="admin-sidebar" id="adminSidebar">
      <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-city"></i></div>
        <div>
          <span class="brand-name">CityZen</span>
          <span class="brand-sub">Administration</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>
        <a href="<?= APP_URL ?>/backoffice" class="sidebar-link <?= preg_match('#/interventions/backoffice(?:/|$)#', (string) ($_SERVER['REQUEST_URI'] ?? '')) && !preg_match('#/interventions/backoffice/.+#', (string) ($_SERVER['REQUEST_URI'] ?? '')) ? 'active' : '' ?>">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <div class="nav-section-label">Gestion</div>
        <a href="<?= APP_URL ?>/backoffice/signalements" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/backoffice/signalements') ? 'active' : '' ?>">
          <i class="fas fa-exclamation-triangle"></i> Signalements
        </a>
        <a href="<?= APP_URL ?>/backoffice/interventions" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/backoffice/interventions') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/backoffice/intervention/') ? 'active' : '' ?>">
          <i class="fas fa-tools"></i> Interventions
        </a>
        <a href="<?= APP_URL ?>/backoffice/techniciens" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/backoffice/techniciens') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/backoffice/technicien/') ? 'active' : '' ?>">
          <i class="fas fa-hard-hat"></i> Techniciens
        </a>
      </nav>

      <div class="sidebar-footer">
        <div class="admin-user-info">
          <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user_prenom'] ?? 'A', 0, 1)) ?></div>
          <div>
            <div class="admin-user-name"><?= htmlspecialchars(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?></div>
            <div class="admin-user-role"><i class="fas fa-shield-alt"></i> Administrateur</div>
          </div>
        </div>
        <a href="<?= APP_URL ?>/" class="btn-front"><i class="fas fa-globe"></i> Voir le site</a>
        <a href="<?= $citizenLogoutHref ?>" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Déconnexion CityZen</a>
        <a href="<?= $citizenPortalHref ?>" class="btn-front" style="margin-top:6px;"><i class="fas fa-sign-in-alt"></i> Portail CityZen</a>
      </div>
    </aside>

    <div class="admin-main">
      <header class="admin-topbar">
        <div>
          <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Administration') ?></div>
          <div class="topbar-breadcrumb">CityZen / <span>Admin</span></div>
        </div>
        <span class="topbar-badge"><i class="fas fa-clock"></i> <?= date('d/m/Y H:i') ?></span>
      </header>

      <?php if (isset($flash) && $flash): ?>
      <div class="flash-admin flash-<?= $flash['type'] ?>">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'times-circle' : 'info-circle') ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <section class="admin-content"><?= $content ?></section>
    </div>
  </div>
</main>
</div>

<link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/copilot.css">
<script>
window.COPILOT_APP_URL = '<?= APP_URL ?>';
window.COPILOT_USER_ROLE = '<?= $_SESSION['user_role'] ?? 'guest' ?>';
window.COPILOT_USER_NAME = '<?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?>';
</script>
<script src="<?= APP_URL ?>/public/assets/js/copilot-kb.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/copilot.js"></script>

<?php cityzen_render_footer(); ?>
